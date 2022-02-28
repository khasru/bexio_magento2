<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bexio\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Setup\Exception;
use Magento\Framework\Encryption\EncryptorInterface;
use Scriptlodge\Bexio\Api\BexioProductRepositoryInterface;
use Scriptlodge\Bexio\Model\BexioProductFactory;


/**
 * Class ProceesOrder
 *
 * @codeCoverageIgnore
 */
class ProceesProduct extends \Magento\Framework\Model\AbstractModel
{
    protected $productUrl = "https://api.bexio.com/2.0/article";
    protected $productSearchUrl = "https://api.bexio.com/2.0/article/search";


    /**
     * Destination constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context                        $context,
        \Magento\Framework\Registry                             $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection = null,
        ProductRepositoryInterface                              $productRepositoryInterface,
        \Magento\Framework\Api\SearchCriteriaBuilder            $searchCriteriaBuilder,
        \Magento\Framework\Api\SortOrderBuilder                 $sortOrderBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface      $scopeConfig,
        \Scriptlodge\Bexio\Helper\Data                          $helperData,
        BexioProductRepositoryInterface                         $bexioProductRepositoryInterface,
        BexioProductFactory                                     $bexioProductFactory,
        EncryptorInterface                                      $encryptorInterface,
        array                                                   $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->encryptorInterface = $encryptorInterface;
        $this->_helperData = $helperData;
        $this->bexioProductRepositoryInterface = $bexioProductRepositoryInterface;
        $this->bexioProductFactory = $bexioProductFactory;
        $this->sortOrderBuilder=$sortOrderBuilder;
    }


    public function sendProductToBexio($productSku = "")
    {
        $response = $productArray = $productJsonData = [];
        $product = "";
        $apiConfig = $this->_helperData->getConfiguration();


        if (isset($apiConfig['enabled']) && $apiConfig['enabled'] != 1) return true;
        $productUrl = $this->productUrl;

        if ($productSku) {
            $product = $this->productRepositoryInterface->get($productSku);
            if ($product->getId()) {
                $this->getArticleBySku($productSku);

                $productMap = $this->getProductMapDataBySku($productSku);
                $productMapId = $bexioArticleId = "";

                if (!empty($productMap)) {
                    foreach ($productMap->getItems() as $item) {
                        $productMapId = $item->getEntityId();
                        $bexioArticleId = $item->getBexioArticleId();
                    }
                    if ($bexioArticleId) {
                        $productUrl = $productUrl . '/' . $bexioArticleId;
                    }
                }

                $productArray = $this->makeProductData($product,$bexioArticleId);
                $productJsonData = json_encode($productArray);

                $response = $this->_helperData->sendCurlRequestToBexio($productUrl, 'POST', $productJsonData);

                $this->updateProductMap($productMapId, $response, $product);
            }

        } else {

        }

    }

    public function makeProductData($product,$articalId="")
    {
        if (empty($product->getId())) return;
        $productData = [];
        if ($articalId == '') {
            if ($product->getTypeId() !== 'virtual') {
                $productData['article_type_id'] = 1;
            } else {
                $productData['article_type_id'] = 2;
            }
        }
        $short_description = "";
        $attributes = $product->getCustomAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute->getAttributeCode() == 'short_description') {
                $short_description = urldecode(htmlentities($attribute->getValue()));
            }
        }
        $productData['intern_code'] = $product->getSku();
        $productData['intern_name'] = $product->getName();
        //$productData['intern_description'] = $short_description;
        $productData['purchase_price'] = $product->getPrice();
        $productData['sale_price'] = $product->getFinalPrice();
        if (!empty($product->getWebsiteIds())) {
            $productData['currency_id'] = $product->getWebsiteIds()[0];
        }
        $productData['tax_income_id'] = null;

        $productData['tax_expense_id'] = null;
        $productData['unit_id'] = null;
        $productData['is_stock'] = false;
        $productData['stock_id'] = null;
        $productData['stock_place_id'] = null;
        $productData['stock_min_nr'] = 0;
        $productData['width'] = null;
        $productData['height'] = null;
        $productData['volume'] = null;
        $productData['html_text'] = null;
        $productData['remarks'] = "Magento Product ID: " . $product->getID();
        return $productData;
    }

    public function getProductMapDataBySku($sku = "")
    {
        $productMap = "";
        if ($sku) {
            $productMap = $this->bexioProductRepositoryInterface->getBySku($sku);
        }
        return $productMap;
    }

    public function getProductMapData()
    {
        $productMap = "";

        $this->searchCriteriaBuilder->addFilter('need_sync', 1, 'eq');
        $sortOrder = $this->sortOrderBuilder->setField('updated_at')->setDirection('ASC')->create();
        $this->searchCriteriaBuilder->setPageSize(10)->setCurrentPage(1);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $productMap = $this->bexioProductRepositoryInterface->getList($searchCriteria);

        return $productMap;
    }

    protected function updateProductMap($productMapId = "", $response, $product = "")
    {
        try {

            if (empty($response)) return;
            $responseArray = json_decode($response);

            $needSync = 0;
            $error = "";
            $id="";
            $productId = $product->getId();
            $sku = $product->getSku();
            $type = $product->getTypeId();

            if (isset($responseArray->error_code) && ($responseArray->error_code == 422 || $responseArray->error_code == 404)) {
                $needSync = 1;
                $error = $responseArray->message;

                $error_intern_code = "/intern_code/i";
                if(preg_match($error_intern_code, $responseArray->errors[0])==1){
                 //   echo $responseArray->errors[0];
                    $this->getArticleBySku($sku);
                    return;
                }

            }elseif(!empty($responseArray)){
              $id = $responseArray->id;
            }



            if (empty($productMapId)) {
                $bexioProduct = $this->bexioProductFactory->create();
                $bexioProduct->setProductId($productId);
                $bexioProduct->setSku($sku);
                $bexioProduct->setBexioArticleId($id);
                $bexioProduct->setError($error);
                $bexioProduct->setProductType($type);
                $bexioProduct->setNeedSync(0);
                $this->bexioProductRepositoryInterface->save($bexioProduct);
            } elseif ($productMapId) {
               // $bexioProduct = $this->bexioProductRepositoryInterface->get($productMapId);
                $bexioProduct = $this->bexioProductFactory->create()->load($productMapId);
                $bexioProduct->setNeedSync($needSync);
                $bexioProduct->setBexioArticleId($id);
                $bexioProduct->setError($error);
                $bexioProduct->save();
                /*$this->bexioProductRepositoryInterface->update($bexioProduct);*/
            }
           return $id;
        } catch (Exception $exception) {
          //  print_r($exception->getMessage());
        }

    }

    public function updateProductMapStatus($product)
    {
        try {

            if (!empty($product)) {
                $productId = $product->getId();
                $sku = $product->getSku();
                $type = $product->getTypeId();

                $bexioProductMaps = $this->bexioProductRepositoryInterface->getBySku($sku);
                $bexioProduct="";
                $productMapId = "";

                if (!empty($bexioProductMaps) && count($bexioProductMaps->getItems()) > 0) {
                    foreach ($bexioProductMaps->getItems() as $productMap) {
                        $productMapId = $productMap->getId();
                       // $bexioProduct=$productMap;
                    }
                    if ($productMapId) {
                        $bexioProduct = $this->bexioProductFactory->create()->load($productMapId);

                        $bexioProduct->setData('need_sync',1);
                      //  $bexioProduct->setError('Test 22Test');
                        $bexioProduct->save();
                     //   $this->bexioProductRepositoryInterface->update($bexioProduct);

                    }
                }elseif(empty($bexioProductMaps)){
                    $bexioProduct = $this->bexioProductFactory->create();
                    $bexioProduct->setProductId($productId);
                    $bexioProduct->setSku($sku);
                    $bexioProduct->setProductType($type);
                    $bexioProduct->setNeedSync(1);
                    $this->bexioProductRepositoryInterface->save($bexioProduct);
                }
            }
        } catch (Exception $exception) {
            print_r($exception->getMessage());
        }
    }

    public function getArticleBySku($sku)
    {

        try {
            $productSearchUrl = $this->productSearchUrl;
            $request_body[] = array('field' => 'intern_code', 'value' => $sku, 'criteria' => '=');
            $requestJson = json_encode($request_body);
            $response = $this->_helperData->sendCurlRequestToBexio($productSearchUrl, 'POST', $requestJson);
            $articalId='';

            if (!empty($response)) {
                $responseArray = json_decode($response);
                foreach ($responseArray as $artical) {
                    if ($sku !== $artical->intern_code) continue;
                    $articalId = (int)$artical->id;

                }
                $productMap = $this->getProductMapDataBySku($sku);
                if (!empty($productMap)) {
                    $productMapId="";
                    foreach ($productMap->getItems() as $item) {
                        $productMapId = $item->getId();
                    }
                    $bexioProduct = $this->bexioProductFactory->create()->load($productMapId);

                    $bexioProduct->setNeedSync(1);
                    $bexioProduct->setBexioArticleId($articalId);
                   // $bexioProduct->save();
                    $this->bexioProductRepositoryInterface->save($bexioProduct);
                    return;

                } else {
                    $product = $this->productRepositoryInterface->get($sku);
                    $productId = $product->getId();
                    $sku = $product->getSku();
                    $type = $product->getTypeId();

                    $bexioProduct = $this->bexioProductFactory->create();
                    $bexioProduct->setProductId($productId);
                    $bexioProduct->setSku($sku);
                    $bexioProduct->setBexioArticleId($articalId);
                    $bexioProduct->setProductType($type);
                    $bexioProduct->setNeedSync(1);
                    $bexioProduct->save();
                    $this->bexioProductRepositoryInterface->save($bexioProduct);

                    return $articalId;
                }

            }
        } catch (Exception $e) {

            print_r($e->getMessage());
        }
    }

    public function getBexioArticleIdByProductId($productId = "")
    {
        if ($productId) {
            $bexioArticleId = "";
            $productMap = $this->bexioProductRepositoryInterface->getByProductId($productId);
            if (!empty($productMap)) {
                foreach ($productMap->getItems() as $item) {
                    $bexioArticleId = $item->getBexioArticleId();
                }
            }
            return $bexioArticleId;
        }
    }

    public function getBexioArticleIdBySku($sku = "")
    {
        if ($sku) {
            $bexioArticleId = "";
            $productMap = $this->bexioProductRepositoryInterface->getBySku($sku);
            if (!empty($productMap)) {
                foreach ($productMap->getItems() as $item) {
                    $bexioArticleId = $item->getBexioArticleId();
                }
            }
            return $bexioArticleId;
        }
    }

}
