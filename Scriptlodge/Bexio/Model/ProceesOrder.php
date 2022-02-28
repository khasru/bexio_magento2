<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bexio\Model;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Setup\Exception;
use Magento\Framework\Encryption\EncryptorInterface;


/**
 * Class ProceesOrder
 *
 * @codeCoverageIgnore
 */
class ProceesOrder extends \Magento\Framework\Model\AbstractModel
{
    protected $orderUrl = "https://api.bexio.com/2.0/kb_order";
    protected $invoiceUrl = "https://api.bexio.com/2.0/kb_invoice";
    protected $deliveryUrl='https://api.bexio.com/2.0/kb_delivery';
    protected $manualentriesUrl='https://api.bexio.com/3.0/accounting/manual_entries';

    protected $_helperData;
    protected $_bexioOrderRepositoryInterface;
    protected $_bexioProductRepositoryInterface;
    protected $_orderRepositoryInterface;
    protected $_proceesCustomer;
    protected $_proceesProduct;
    const  bank_account_id=2;
    const shipping_acount_id=260;
    const coffee_acount_id=101;
    const noncoffee_acount_id=235;

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
        \Magento\Framework\Api\SearchCriteriaBuilder            $searchCriteriaBuilder,
        \Magento\Framework\Api\SortOrderBuilder                 $sortOrderBuilder,
        \Scriptlodge\Bexio\Helper\Data                          $helperData,
        \Scriptlodge\Bexio\Api\BexioOrderRepositoryInterface    $bexioOrderRepositoryInterface,
        \Scriptlodge\Bexio\Api\BexioProductRepositoryInterface  $bexioProductRepositoryInterface,
        OrderRepositoryInterface                                $orderRepositoryInterface,
        \Scriptlodge\Bexio\Model\ProceesCustomer                $proceesCustomer,
        \Scriptlodge\Bexio\Model\ProceesProduct                 $proceesProduct,
        \Scriptlodge\Bexio\Model\BexioOrderFactory              $bexioOrderFactory,

        array                                                   $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_helperData = $helperData;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder=$sortOrderBuilder;
        $this->_bexioOrderRepositoryInterface = $bexioOrderRepositoryInterface;
        $this->_bexioProductRepositoryInterface = $bexioProductRepositoryInterface;
        $this->_orderRepositoryInterface = $orderRepositoryInterface;
        $this->_proceesCustomer = $proceesCustomer;
        $this->_proceesProduct = $proceesProduct;
        $this->_bexioOrderFactory = $bexioOrderFactory;
    }


    public function sendOrderToBexio($orderId = '')
    {
        $response = $orderArray = $orderJsonData = [];
        $order = "";
        $bexioOrderId = $orderMapId = "";

        $apiConfig = $this->_helperData->getConfiguration();

        if (isset($apiConfig['enabled']) && $apiConfig['enabled'] != 1) return;
        $orderUrl = $this->orderUrl;

        if ($orderId) {
            $order = $this->_orderRepositoryInterface->get($orderId);
            if(!in_array($order->getStoreId(),array(1,2,3))){
                //return $requestData;
                return null;
            }
//echo $order->getId();
            if($order->getId()<=11921){
                return null;
            }

            $orderArray = $this->makeOrderData($order, $bexioOrderId);
          //echo "<pre>";
         /*       print_r($orderArray);
                exit('I am heere');*/

            $orderJsonData = json_encode($orderArray);

            $orderMap = $this->getOrderMapDataById($orderId);

            if (!empty($orderMap)) {
                foreach ($orderMap->getItems() as $item) {
                    $orderMapId = $item->getId();
                    $bexioOrderId = $item->getBexioOrderId();
                }
            }
            $response = $this->_helperData->sendCurlRequestToBexio($orderUrl, 'POST', $orderJsonData);
           /*print_r($response);*/
            $Id = $this->updateOrderMap($orderMapId, $response, $order);
            ### create invoice
            $this->sendOrderInvoiceToBexio($orderId);
            return $Id;
        }
    }

    public function makeOrderData($order, $bexioOrderId = "")
    {
        if (empty($order->getId())) return;
        $orderData = [];
        $positions = $_positionData = [];

        $bexioContactId = $this->getBexioContactIdByOrder($order);

        $orderData['user_id'] = 1;
        if($bexioContactId){
            $orderData['contact_id'] = $bexioContactId;
        }

        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $bank_account_id=self::bank_account_id;
        $account_id=self::coffee_acount_id;

        $payment_type_id = 6;
        if ($method->getCode() == 'powerpaycw_openinvoice' || $method->getCode() == 'powerpaycw_openinvoiceb2b') {
            $payment_type_id = 4;
        } else {
            $payment_type_id = 6;
        }



        $orderData['title'] = $order->getIncrementId();
        $orderData['pr_project_id'] = 1;
        $orderData['language_id'] = 1;
        $orderData['bank_account_id'] = $bank_account_id;
        $orderData['currency_id'] = 1;
        $orderData['payment_type_id'] = $payment_type_id;
        $orderData['mwst_type'] = 0;
        $orderData['mwst_is_net'] = 1;
        $orderData['show_position_taxes'] = 1;


        $tax_id = 17;
        foreach ($order->getAllVisibleItems() as $item) {

            if ($item->getTaxPercent() > 2.5) {
                $tax_id = 16;
                $account_id=self::noncoffee_acount_id;
            }elseif ($item->getTaxAmount() == 0){
                $tax_id = 3;
            }


            if ($item->getProductType() == 'bundle') {
                $bexioArticleId = $this->_proceesProduct->getBexioArticleIdByProductId($item->getProductId());
                if (empty($bexioArticleId)) {
                    $bexioArticleId = $this->_proceesProduct->sendProductToBexio($item->getSku());
                }

                foreach ($item->getChildrenItems() as $childItem) {
                    $tax_id = 17;
                    if ($childItem->getTaxPercent() > 2.5) {
                        $tax_id = 16;
                        $account_id=self::noncoffee_acount_id;
                    }
                    $qty = (int)$childItem->getQtyOrdered();
                    $bexioArticleChildId = $this->_proceesProduct->getBexioArticleIdByProductId($childItem->getProductId());
                    /*if ($bexioArticleChildId == "") {
                        $bexioArticleChildId = $this->_proceesProduct->sendProductToBexio($childItem->getSku());
                    }*/
                    if (empty($bexioArticleChildId)) {
                        $bexioArticleChildId = $this->_proceesProduct->getArticleBySku($childItem->getSku());
                        if(empty($bexioArticleChildId)){
                            $bexioArticleChildId = $this->_proceesProduct->sendProductToBexio($childItem->getSku());
                        }
                    }


                    $discount_in_percent=number_format($item->getDiscountPercent(),2);
                    $_positionData['article_id'] = $bexioArticleChildId;
                    $_positionData['unit_price'] = $childItem->getPrice();
                    $_positionData['type'] = "KbPositionArticle";
                    $_positionData['amount'] = $qty;
                    $_positionData['tax_id'] = $tax_id;
                    if($account_id){
                        $_positionData['account_id'] = $account_id;
                    }
                    $_positionData['discount_in_percent'] = $discount_in_percent;

                  //  $_positionData['parent_id'] = $bexioArticleId;
                    $positions[] = $_positionData;
                }
            } else {

                $qty = (int)$item->getQtyOrdered();
                $bexioArticleId = "";

                $productMaps = $this->_proceesProduct->getProductMapDataBySku($item->getSku());

                if (!empty($productMaps)) {
                    foreach ($productMaps->getItems() as $_productmap) {
                        $bexioArticleId = $_productmap->getBexioArticleId();
                    }
                }

                if (empty($bexioArticleId)) {
                    $bexioArticleId = $this->_proceesProduct->getArticleBySku($item->getSku());
                    if(empty($bexioArticleId)){
                        $bexioArticleId = $this->_proceesProduct->sendProductToBexio($item->getSku());
                    }
                }

                $discount_in_percent=number_format($item->getDiscountPercent(),2);
                $_positionData['article_id'] = $bexioArticleId;
                $_positionData['unit_price'] = $item->getPrice();
                $_positionData['type'] = "KbPositionArticle";
                $_positionData['amount'] = $qty;
                $_positionData['tax_id'] = $tax_id;
                if($account_id){
                    $_positionData['account_id'] = $account_id;
                }
                $_positionData['discount_in_percent'] = $discount_in_percent;
                $positions[] = $_positionData;
            }

        }

        $gCardOrder = $order->getExtensionAttributes()->getAmGiftcardOrder();

        if(!empty($gCardOrder->getGiftCards())){
            foreach ($gCardOrder->getGiftCards() as $card) {
                $_positionGiftCardData['type']='KbPositionDiscount';
             //   $_positionGiftCardData['is_percentual'] =1;
                $_positionGiftCardData['value'] =$card['amount'];
                $_positionGiftCardData['text']='Gift Card : '.$card['code'];
                $_positionsGiftArray[]=$_positionGiftCardData;
                $positions= array_merge($positions,$_positionsGiftArray);
            }
        }

        if ($order->getShippingAmount() > 0) {
            $taxpercent = 7.7;
            $shippingAmount = ($order->getShippingAmount() * 100) / (100 + $taxpercent);
            $shippingAmount = number_format($shippingAmount, 2);
        } else {
            $shippingAmount = 0;
        }
        $shippingAcount_id=self::shipping_acount_id;
        ## add shipping fee
        $_positionShippingData['type']='KbPositionCustom';
        $_positionShippingData['amount']=1;
        //$_positionShippingData['unit_price'] =$order->getBaseShippingInclTax();
        $_positionShippingData['unit_price'] =$shippingAmount;
        $_positionShippingData['tax_id']=16;
        $_positionShippingData['account_id'] = $shippingAcount_id;
        $_positionShippingData['text']='Delivery : '.$order->getShippingDescription();

        $_positionsArray[]=$_positionShippingData;
        $positions= array_merge($positions,$_positionsArray);

        $orderData['positions'] = $positions;

        return $orderData;
    }

    public function getBexioContactIdByOrder($order)
    {
        $bexioContactId = "";
        $email = $order->getCustomerEmail();
        $webSiteId = $order->getStore()->getWebsiteId();
        $customerMap = $this->_proceesCustomer->getCustomerMapData("", $email, $webSiteId);
        $response=$this->_proceesCustomer->searchContact($email,$webSiteId);

        if(!empty($response)){
            $response=json_decode($response);
            foreach ($response as $bexioCustomer){
                if($bexioCustomer->id){
                    $bexioContactId=$bexioCustomer->id;
                }
            }
        }
        if($bexioContactId){
            return $bexioContactId;
        }

        if (!empty($customerMap)) {
            foreach ($customerMap->getItems() as $customer) {
                $bexioContactId = $customer->getBexioContactId();

            }
        }


        if (empty($bexioContactId)) {

            $address = $order->getBillingAddress()->getStreetLine(1) . ' ' . $order->getBillingAddress()->getStreetLine(2);
            $customerArray['first_name'] = ($order->getCustomerFirstname())?$order->getCustomerFirstname():$order->getBillingAddress()->getFirstName();
            $customerArray['last_name'] = ($order->getCustomerLastname())?$order->getCustomerLastname():$order->getBillingAddress()->getLastName();
            $customerArray['email'] = $order->getCustomerEmail();
            $customerArray['prefix'] = $order->getCustomerPrefix();
            $customerArray['address'] = $address;
            $customerArray['postcode'] = $order->getBillingAddress()->getPostcode();
            $customerArray['city'] = $order->getBillingAddress()->getCity();
            $customerArray['country_id'] = $this->_proceesCustomer->getBexioCountryId($order->getBillingAddress()->getCountryId());
            $customerArray['phone_mobile'] = $order->getBillingAddress()->getTelephone();
            $customerArray['fax'] = $order->getBillingAddress()->getFax();
            $customerArray['web_site_id'] = $webSiteId;
            $bexioContactId = $this->_proceesCustomer->proceesRequest($customerArray);

        }
        return $bexioContactId;
    }

    public function updateOrderMap($orderMapId = "", $response, $order)
    {
        try {

            if (empty($response)) return;
            $responseArray = json_decode($response);

            $needSync = 0;
            $error = "";
            $bexioOrderid = $positions="";

            $orderId = $order->getId();
            $orderIncrementId = $order->getIncrementId();

            if (isset($responseArray->error_code) && ($responseArray->error_code == 422 || $responseArray->error_code == 404)) {
                $needSync = 1;
                $error = $responseArray->message;


            } elseif (!empty($responseArray)) {
                $bexioOrderid = $responseArray->id;
                $positions=json_encode($responseArray->positions);

            }

            if (empty($orderMapId)) {
                $_bexioOrderId="";
                $orderMap=$this->_bexioOrderRepositoryInterface->getByOrderId($orderId);
                if (!empty($orderMap)) {
                    foreach ($orderMap->getItems() as $item) {
                        $_bexioOrderId = $item->getBexioOrderId();
                    }
                    if ($_bexioOrderId) {
                        return $_bexioOrderId;
                    }
                }
                if(empty($_bexioOrderId)) {
                    $bexioOrder = $this->_bexioOrderFactory->create();
                    $bexioOrder->setOrderId($orderId);
                    $bexioOrder->setOrderIncrementId($orderIncrementId);
                    $bexioOrder->setBexioOrderId($bexioOrderid);
                    $bexioOrder->setOrderPositions($positions);
                    $bexioOrder->setError($error);
                    $bexioOrder->setNeedSync(1);
                    $this->_bexioOrderRepositoryInterface->save($bexioOrder);
                }
            } elseif ($orderMapId) {
                // $bexioProduct = $this->bexioProductRepositoryInterface->get($productMapId);
                $bexioOrder = $this->_bexioOrderFactory->create()->load($orderMapId);
                $bexioOrder->setNeedSync($needSync);
                $bexioOrder->setBexioOrderId($bexioOrderid);
                $bexioOrder->setError($error);
                $bexioOrder->setOrderPositions($positions);
                $bexioOrder->save();

            }
            return $bexioOrderid;
        } catch (Exception $exception) {
            //  print_r($exception->getMessage());
        }

    }

    public function updateOrderInvoiceMap($orderMapId, $response, $invoiceId = "")
    {
        try {
            if (empty($response)) return;
            $responseArray = json_decode($response);

            if (isset($responseArray->error_code) && ($responseArray->error_code == 422 || $responseArray->error_code == 404)) {
                $needSync = 1;
                $error = $responseArray->message;
            } elseif (!empty($responseArray)) {
                /* $bexioOrderid = $responseArray->id;
                 $positions = json_encode($responseArray->positions);*/

                $bexioOrder = $this->_bexioOrderFactory->create()->load($orderMapId);

                if (empty($bexioOrder->getBexioInvoice())) {
                    $bexioOrder->setBexioInvoice($response);
                } else {
                    $_invoice = $bexioOrder->getBexioInvoice();
                }
                $bexioOrder->save();
            }


        } catch (Exception $exception) {
            //  print_r($exception->getMessage());
        }
    }

    public function updateOrderDeliveryMap($orderMapId, $response, $invoiceId = "")
    {
        $bexioshipmentid = "";
        try {
            if (empty($response)) return;
            $responseArray = json_decode($response);

            if (isset($responseArray->error_code) && ($responseArray->error_code == 422 || $responseArray->error_code == 404)) {
                $needSync = 1;
                $error = $responseArray->message;
            } elseif (!empty($responseArray)) {
                $bexioshipmentid = $responseArray->id;

                $bexioOrder = $this->_bexioOrderFactory->create()->load($orderMapId);

                if (empty($bexioOrder->getBexioShipment())) {
                    $bexioOrder->setBexioShipment($response);
                } else {
                    $_invoice = $bexioOrder->getBexioShipment();
                }
                $bexioOrder->save();
                return $bexioshipmentid;
            }
        } catch (Exception $exception) {
            //  print_r($exception->getMessage());
        }
    }

    public function getOrderMapDataById($orderId = "")
    {
        $orderMap = "";
        if ($orderId) {
            $orderMap = $this->_bexioOrderRepositoryInterface->getByOrderId($orderId);
        }
        return $orderMap;
    }


    public function sendOrderInvoiceToBexio($orderId = "")
    {
        $response = $invoiceJsonData = [];
        $bexioOrderId = $orderMapId = $bexioOrderPositions = "";

        $apiConfig = $this->_helperData->getConfiguration();
        if (isset($apiConfig['enabled']) && $apiConfig['enabled'] != 1) return;
        $orderUrl = $this->orderUrl;

        if ($orderId) {
            $orderMap = $this->getOrderMapDataById($orderId);

            if (!empty($orderMap)) {
                foreach ($orderMap->getItems() as $item) {
                    $orderMapId = $item->getId();
                    $bexioOrderId = $item->getBexioOrderId();
                    $bexioOrderPositions = $item->getBexioOrderPositions();
                    $bexioInvoice = $item->getBexioInvoice();
                    $exioShipment = $item->getBexioShipment();
                }
            }

            #### https://api.bexio.com/2.0/kb_order/4/invoice
            $invoiceUrl = $orderUrl . '/' . $bexioOrderId . '/invoice';

            $invoicePositions = $this->makeInvoiceData($orderId, $bexioOrderPositions);

            if (!empty($invoicePositions)) {
                $position['positions'] = $invoicePositions;
                $invoiceJsonData = json_encode($position);

                $response = $this->_helperData->sendCurlRequestToBexio($invoiceUrl, 'POST', $invoiceJsonData);
                //print_r($response);
                // $response='{"id":4,"document_nr":"RE-00004","title":"","contact_id":14,"contact_sub_id":null,"user_id":1,"project_id":1,"logopaper_id":1,"language_id":1,"bank_account_id":1,"currency_id":1,"payment_type_id":3,"header":"Guten Tag khasru MiahDanke f\u00fcr Ihr Vertrauen. Ihre Rechnung setzt sich wie folgt zusammen:","footer":"Sie haben Fragen? Melden Sie sich bei uns.Freundliche Gr\u00fcssekhasru MIah","total_gross":"24.29","total_net":"24.3","total_taxes":"0.0000","total_received_payments":"0","total_credit_vouchers":"0","total_remaining_payments":"24.3000","total":"24.3","total_rounding_difference":0,"mwst_type":3,"mwst_is_net":true,"show_position_taxes":false,"is_valid_from":"2021-11-11","is_valid_to":"2021-12-10","contact_address":"khasru Miah\nTest\n1234 ch\nSchweiz","kb_item_status_id":7,"reference":null,"api_reference":null,"viewed_by_client_at":null,"updated_at":"2021-11-11 07:44:15","esr_id":4,"qr_invoice_id":4,"template_slug":"","taxs":[],"positions":[{"id":8,"type":"KbPositionArticle","amount":"1","unit_id":null,"account_id":101,"unit_name":null,"tax_id":16,"tax_value":"7.70","text":"Kenner 1000g<\/strong>Produktcode: Kenner 1000g","unit_price":"24.290000","discount_in_percent":null,"position_total":"24.29","pos":"1","internal_pos":1,"parent_id":null,"is_optional":false,"article_id":13}],"network_link":""}';
                return $this->updateOrderInvoiceMap($orderMapId, $response);
            }
        }
    }

    public function getInvoiceDetails($orderId)
    {
        $orderdetails = $this->_orderRepositoryInterface->get($orderId);
        $invoices = [];
        foreach ($orderdetails->getInvoiceCollection() as $invoice) {
            $invoices[] = $invoice;
        }
        return $invoices;
    }


    public function makeInvoiceData($orderId, $bexioOrderPositions)
    {
        $position = [];
        $order = $this->_orderRepositoryInterface->get($orderId);
        $bexioOrderPositions = json_decode($bexioOrderPositions);

        foreach ($order->getInvoiceCollection() as $invoice) {
            $positionData = [];
            foreach ($invoice->getItems() as $item) {
                $productId = $item->getProductId();
                $sku = $item->getSku();
                $id = $amount = '';
                if (!empty($bexioOrderPositions)) {
                    foreach ($bexioOrderPositions as $positionsData) {
                        if(empty($positionsData)) continue;
                        $id = $positionsData->id;
                        $amount = (isset($positionsData->amount))?$positionsData->amount:0;
                        $type = $positionsData->type;

                        if ($type == 'KbPositionArticle') {
                            $article_id = $positionsData->article_id;
                            $_bexio_article_id = $this->_proceesProduct->getBexioArticleIdBySku($sku);
                            if ($_bexio_article_id == $article_id) {
                                $positionData['type'] = 'KbPositionArticle';
                                $positionData['id'] = $id;
                                $positionData['amount'] = $amount;
                                if(empty($positionData)) continue;
                                $position[] = $positionData;
                            }
                        } elseif($type =='KbPositionCustom') {
                           /* $positionData['type'] = $type;
                            $positionData['id'] = $id;
                            $positionData['amount'] = $amount*/;
                        }

                    }

                }
            }
            $positionData_delivery=[];
            if (!empty($bexioOrderPositions)) {
                foreach ($bexioOrderPositions as $positionsData) {
                    if(empty($positionsData)) continue;
                    $id = $positionsData->id;
                    $amount = (isset($positionsData->amount))?$positionsData->amount:0;
                    $type = $positionsData->type;
                    if ($type == 'KbPositionCustom') {
                        $positionData_delivery['type'] = $type;
                        $positionData_delivery['id'] = $id;
                        $positionData_delivery['amount'] = $amount;
                        if(!empty($positionData_delivery)){
                            $position[]=$positionData_delivery;
                            //array_merge($position,$positionData_delivery);
                        }
                    }elseif($type =='KbPositionDiscount') {
                        $amount = (isset($positionsData->value))?$positionsData->value:0;
                        $positionData_discount['type'] = 'KbPositionDiscount';
                        $positionData_discount['id'] = $id;
                        //  $positionData_discount['amount'] = 1;
                        if(!empty($positionData_discount)){
                            $position[]=$positionData_discount;
                        }
                    }
                }
            }
        }
        $position = array_unique($position, SORT_REGULAR);
        return $position;
    }

    public function issueAnInvoiceToBexio(){
        //https://api.bexio.com/2.0/kb_invoice/5/issue
        $invoiceUrl= $this->invoiceUrl;
        $orderMap = $invoiceIssueUrl="";
        $bank_account_id=self::bank_account_id;
        $to = date("Y-m-d h:i:s");
        $from = strtotime('-20 day', strtotime($to));
        $from = date('Y-m-d h:i:s', $from);


        $this->searchCriteriaBuilder->addFilter('bexio_invoice_send', 1, 'neq');
        $this->searchCriteriaBuilder->addFilter('created_at', $from, 'gteq');
        $sortOrder = $this->sortOrderBuilder->setField('updated_at')->setDirection('ASC')->create();

        $this->searchCriteriaBuilder->setPageSize(1000)->setCurrentPage(1);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $orderMap = $this->_bexioOrderRepositoryInterface->getList($searchCriteria);
        if(!empty($orderMap)){
            foreach ($orderMap->getItems() as $bexioOrder){

                $orderMapId= $bexioOrder->getId();
                $orderId= $bexioOrder->getOrderId();
                $bexioInvoice=    $bexioOrder->getBexioInvoice();
                $order = $this->_orderRepositoryInterface->get($orderId);
                $payment = $order->getPayment();
                $method = $payment->getMethodInstance();
               // echo $method->getCode().'----';
                if ($method->getCode() == 'powerpaycw_openinvoice' || $method->getCode() == 'powerpaycw_openinvoiceb2b') {
                    continue;
                }
                $paymentCode = $method->getCode();
                $pos = strpos($paymentCode, 'paypal');
                if ($pos !== false) {
                    $payment_service_id=1;
                }else{
                    $payment_service_id=3;
                }


                if(!empty($bexioInvoice)){
                    $invoiceData=json_decode($bexioInvoice);

                    if(isset($invoiceData->id)){
                       $bexioInvoiceId=$invoiceData->id;
                        $total=$invoiceData->total_remaining_payments;
                   	    $invoiceIssueUrl= $invoiceUrl.'/'.$bexioInvoiceId.'/issue';
                        $response = $this->_helperData->sendCurlRequestToBexio($invoiceIssueUrl, 'POST', '');
                       //   print_r($response);
                     //  exit();

                        $invoiceMarkSentUrl= $invoiceUrl.'/'.$bexioInvoiceId.'/mark_as_sent';
                        $responseSend = $this->_helperData->sendCurlRequestToBexio($invoiceMarkSentUrl, 'POST', '');
                      //  print_r($responseSend);

                        $paymentData['date']=date('Y-m-d');
                        $paymentData['value']=number_format($total,2, '.', '');
                        $paymentData['bank_account_id']=$bank_account_id;
                        $paymentData['payment_service_id']=$payment_service_id;
                        $paymentJsonData=json_encode($paymentData);
                    //    print_r(json_encode($paymentData));

                        $invoicePaymentUrl= $invoiceUrl.'/'.$bexioInvoiceId.'/payment';
                        $responsePayment = $this->_helperData->sendCurlRequestToBexio($invoicePaymentUrl, 'POST', $paymentJsonData);
                        /*$responsePayment='{"id":2,"date":"2021-11-10","value":"27.2","bank_account_id":1,"title":"Zahlungseingang","payment_service_id":null,"is_client_account_redemption":false,"is_cash_discount":false,"kb_invoice_id":1,"kb_credit_voucher_id":null,"kb_bill_id":null,"kb_credit_voucher_text":""}';
                        print_r($responsePayment);*/
                       $responsearray= json_decode($responsePayment);
                       if(isset($responsearray->id)){
                           $bexioOrder = $this->_bexioOrderFactory->create()->load($orderMapId);
                           $bexioOrder->setBexioInvoiceSend(1);
                           $bexioOrder->save();
                       }
                    }
                }
              //  print_r(json_decode($bexioInvoice));
            }
        }
        return $orderMap;
    }

    public function sendOrDerdeliveryToBexio($orderId = "")
    {
        $response = $invoiceJsonData = [];
        $bexioOrderId = $orderMapId = $bexioOrderPositions = "";

        $apiConfig = $this->_helperData->getConfiguration();
        if (isset($apiConfig['enabled']) && $apiConfig['enabled'] != 1) return;
        $orderUrl = $this->orderUrl;

        if ($orderId) {
            $orderMap = $this->getOrderMapDataById($orderId);

            if (!empty($orderMap)) {
                foreach ($orderMap->getItems() as $item) {
                    $orderMapId = $item->getId();
                    $bexioOrderId = $item->getBexioOrderId();
                    $bexioOrderPositions = $item->getBexioOrderPositions();
                    $bexioInvoice = $item->getBexioInvoice();
                    $exioShipment = $item->getBexioShipment();
                }
            }
            if(empty($bexioOrderPositions)) return ;
            #### https://api.bexio.com/2.0/kb_order/4/delivery
            $deliveryUrl = $orderUrl . '/' . $bexioOrderId . '/delivery';
            $deliveryPositions = $this->makeShipmentData($orderId, $bexioOrderPositions);


            if (!empty($deliveryPositions)) {
                $position['positions'] = $deliveryPositions;
                $deliveryJsonData = json_encode($position);
                $response = $this->_helperData->sendCurlRequestToBexio($deliveryUrl, 'POST', $deliveryJsonData);
              //  print_r($response);
                return $this->updateOrderDeliveryMap($orderMapId, $response);
            }
        }
    }


    public function makeShipmentData($orderId, $bexioOrderPositions)
    {

        $position = $_positionData = [];
        $order = $this->_orderRepositoryInterface->get($orderId);
        $bexioOrderPositions = json_decode($bexioOrderPositions);
        foreach ($order->getShipmentsCollection() as $shipments) {

            foreach ($shipments->getItemsCollection() as $kay => $item) {
                $productId = $item->getProductId();

                $id = $amount = "";

                if (!empty($bexioOrderPositions)) {
                    foreach ($bexioOrderPositions as $positionsData) {
                        $id = $positionsData->id;
                        $amount = (isset($positionsData->amount))?$positionsData->amount:0;
                        $type = $positionsData->type;
                        if ($type == 'KbPositionArticle') {
                            $article_id = $positionsData->article_id;
                            $_bexio_article_id = $this->_proceesProduct->getBexioArticleIdByProductId($productId);

                         //   if ($_bexio_article_id == $article_id) {
                                $_positionData['type'] = "KbPositionArticle";
                                $_positionData['id'] = $id;
                                $_positionData['amount'] = $amount;
                                $position[] = $_positionData;
                         //   }
                        } elseif ($type == 'KbPositionCustom') {
                            $_positionData['type'] = "KbPositionCustom";
                            $_positionData['id'] = $id;
                            $_positionData['amount'] = $amount;
                            $position[] = $_positionData;
                        }
                        /*  if(empty($_positionData)) continue;
                          $position[] = $_positionData;*/
                    }
                }
            }
        }
        $position = array_unique($position, SORT_REGULAR);
        return $position;
    }

    public function issueDeliveryToBexio(){
        ##https://api.bexio.com/2.0/kb_delivery/6/issue
        $deliveryUrl= $this->deliveryUrl;
        $orderMap = $invoiceIssueUrl="";

        $to = date("Y-m-d h:i:s");
        $from = strtotime('-20 day', strtotime($to));
        $from = date('Y-m-d h:i:s', $from);

        $this->searchCriteriaBuilder->addFilter('bexio_shipment', null, 'neq');
        $this->searchCriteriaBuilder->addFilter('bexio_shipment_done', 1, 'neq');
        $this->searchCriteriaBuilder->addFilter('created_at', $from, 'gteq');
        $sortOrder = $this->sortOrderBuilder->setField('updated_at')->setDirection('ASC')->create();
        $this->searchCriteriaBuilder->setPageSize(1000)->setCurrentPage(1);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $orderMap = $this->_bexioOrderRepositoryInterface->getList($searchCriteria);
        if(!empty($orderMap)){
            foreach ($orderMap->getItems() as $bexioOrder){
                $orderMapId= $bexioOrder->getId();
                $bexioShipment=    $bexioOrder->getBexioShipment();
                if(!empty($bexioShipment)) {
                    $shipmentData = json_decode($bexioShipment);
                    //   print_r($invoiceData);
                    if (isset($shipmentData->id)) {
                        $bexioDeliveryId = $shipmentData->id;

                        $deliveryIssueUrl = $deliveryUrl . '/' . $bexioDeliveryId . '/issue';
                        $response = $this->_helperData->sendCurlRequestToBexio($deliveryIssueUrl, 'POST', '');
                       // $response= '{"success":true}';
                        $responseArray=json_decode($response);

                        if (isset($responseArray->success) && $responseArray->success==1) {
                            $bexioOrder = $this->_bexioOrderFactory->create()->load($orderMapId);
                            $bexioOrder->setBexioShipmentDone(1);
                            $bexioOrder->save();
                        }
                    }
                }
            }
        }
        return $orderMap;
    }


    public function sendOrManualEntryToBexio($orderId,$orderMapId){
        $requestArray=[];
        $apiConfig = $this->_helperData->getConfiguration();
        if (isset($apiConfig['enabled']) && $apiConfig['enabled'] != 1) return;
        $manualentriesUrl = $this->manualentriesUrl;
        if ($orderId) {
            $order = $this->_orderRepositoryInterface->get($orderId);
            if (!in_array($order->getStoreId(), array(1, 2, 3))) {
                return null;
            }


            $payment = $order->getPayment();
            $method = $payment->getMethodInstance();
            if ($method->getCode() == 'powerpaycw_openinvoice' || $method->getCode() == 'powerpaycw_openinvoiceb2b') {
                return null;
            }
            $debit_account_id=261;
            $credit_account_id=114;
            $currency_id=1;
            $currency_factor=1;
            $incrementId=$order->getIncrementId();
            $amount=$order->getGrandTotal();

            $requestArray['type']='manual_single_entry';
            $requestArray['date']=date('Y-m-d');
            $requestArray['reference_nr']=$incrementId;

            $entries=array(
                'debit_account_id'=>$debit_account_id,
                'credit_account_id'=>$credit_account_id,
                'tax_id'=>17,
                'tax_account_id'=>$credit_account_id,
                'description'=>'Payment for order: '.$incrementId,
                'amount'=>$amount,
                'currency_id'=>$currency_id,
                'currency_factor'=>$currency_factor
            );
            $requestArray['entries'][]=$entries;
            /*$tax_id = 17;
            foreach ($order->getAllVisibleItems() as $item) {

                if ($item->getTaxPercent() > 2.5) {
                    $tax_id = 16;
                }


                if ($item->getProductType() == 'bundle') {

                    foreach ($item->getChildrenItems() as $childItem) {
                        $price = $childItem->getPrice();
                        $sku = $childItem->getSku();

                        $_entries['debit_account_id'] = $debit_account_id;
                        $_entries['credit_account_id'] = $credit_account_id;
                        $_entries['currency_id'] = $currency_id;
                        $_entries['currency_factor'] = $currency_factor;

                        $_entries['tax_id'] = $tax_id;
                        $_entries['amount'] = $price;
                        //$_entries['description'] = 'Payment for order: ' . $incrementId . 'sku :' . $sku;
                        $_entries['description'] = 'Payment for order: ' . $incrementId;
                        $entries[] = $_entries;
                    }
                } else {

                    $price = $item->getPrice();
                    $sku = $item->getSku();

                    $_entries['debit_account_id'] = $debit_account_id;
                    $_entries['credit_account_id'] = $credit_account_id;
                    $_entries['currency_id'] = $currency_id;
                    $_entries['currency_factor'] = $currency_factor;

                    $_entries['tax_id'] = $tax_id;
                    $_entries['amount'] = $price;
                    $_entries['description'] = 'Payment for order: ' . $incrementId . ' sku :' . $sku;
                    $entries[] = $_entries;

                }

            }
            $requestArray['entries']=$entries;*/


            $requestJsonData = json_encode($requestArray);

            $response = $this->_helperData->sendCurlRequestToBexio($manualentriesUrl, 'POST', $requestJsonData);
            $responseArray = json_decode($response);
            if (isset($responseArray->error_code) && ($responseArray->error_code == 422 || $responseArray->error_code == 404)) {
                $error = $responseArray->message;
            } else {
                $bexioOrder = $this->_bexioOrderFactory->create()->load($orderMapId);
                $bexioOrder->setData('bexio_manual_entry', $response);
                $bexioOrder->save();

            }

        }

    }

}
