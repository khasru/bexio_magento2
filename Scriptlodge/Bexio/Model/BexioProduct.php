<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bexio\Model;

use Scriptlodge\Bexio\Api\Data\BexioProductInterface;

/**
 * Class BexioProduct
 *
 * @codeCoverageIgnore
 */
class BexioProduct extends \Magento\Framework\Model\AbstractModel implements
    BexioProductInterface
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Scriptlodge\Bexio\Model\ResourceModel\BexioProduct::class);
    }


    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->_getData(BexioProductInterface::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId)
    {
        $this->setData(BexioProductInterface::ENTITY_ID, $entityId);

        return $this;
    }


    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->_getData(BexioProductInterface::PRODUCT_ID);
    }

    /**
     * @param int $productId
     * @return mixed
     */
    public function setProductId($productId)
    {
        $this->setData(BexioProductInterface::PRODUCT_ID, $productId);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSku()
    {
        return $this->_getData(BexioProductInterface::SKU);
    }

    /**
     * @param string $sku
     * @return mixed
     */
    public function setSku($sku)
    {
        $this->setData(BexioProductInterface::SKU, $sku);

        return $this;
    }



    /**
     * @return mixed
     */
    public function getBexioArticleId()
    {
        return $this->_getData(BexioProductInterface::BEXIO_ARTICLE_ID);
    }

    /**
     * @param string $bexioArticleId
     * @return mixed
     */
    public function setBexioArticleId($bexioArticleId)
    {
        $this->setData(BexioProductInterface::BEXIO_ARTICLE_ID, $bexioArticleId);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNeedSync()
    {
        return $this->_getData(BexioProductInterface::NEED_SYNC);
    }

    /**
     * @param int $needSync
     * @return mixed
     */
    public function setNeedSync($needSync)
    {
        $this->setData(BexioProductInterface::NEED_SYNC, $needSync);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductType()
    {
        return $this->_getData(BexioProductInterface::PRODUCT_TYPE);
    }

    /**
     * @param string $productType
     * @return mixed
     */
    public function setProductType($productType)
    {
        $this->setData(BexioProductInterface::PRODUCT_TYPE, $productType);

        return $this;
    }


    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->_getData(BexioProductInterface::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return mixed
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(BexioProductInterface::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->_getData(BexioProductInterface::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return mixed
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(BexioProductInterface::UPDATED_AT, $updatedAt);

        return $this;
    }

    public function getError()
    {
        return $this->_getData(BexioProductInterface::ERROR);
    }

    public function setError($error)
    {
        $this->setData(BexioProductInterface::ERROR, $error);

        return $this;
    }
}
