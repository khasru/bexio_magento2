<?php

namespace Scriptlodge\Bexio\Api\Data;

/**
 * Interface BexioCustomerInterface
 *
 * @package Scriptlodge\Bexio\Api\Data
 */
interface BexioProductInterface
{
    /**#@+
     * Constants defined for keys of data array
     */

    const ENTITY_ID = 'entity_id';
    const PRODUCT_ID = 'product_id';
    const SKU = 'sku';
    const BEXIO_ARTICLE_ID = 'bexio_article_id';
    const PRODUCT_TYPE = 'product_type';
    const NEED_SYNC = 'need_sync';
    const ERROR = 'error';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    const TABLE_NAME = 'bexio_product';

    /**#@-*/

    /**
     * Get id
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set id
     * @param int $entityId
     * @return \Scriptlodge\Bexio\Api\Data\BexioProductInterface
     */
    public function setEntityId($entityId);

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $productId
     *
     * @return \Scriptlodge\Bexio\Api\Data\BexioProductInterface
     */
    public function setProductId($productId);

    /**
     * @return string
     */
    public function getSku();

    /**
     * @param string $sku
     *
     * @return $this
     */
    public function setSku($sku);


    /**
     * @return int
     */
    public function getBexioArticleId();

    /**
     * @param string $bexioArticleId
     *
     * @return $this
     */
    public function setBexioArticleId($bexioArticleId);

    /**
     * @return int
     */
    public function getNeedSync();

    /**
     * @param int $needSync
     * @return $this
     */
    public function setNeedSync($needSync);

    /**
     * @return string
     */
    public function getError();

    /**
     * @param string $error
     * @return $this
     */
    public function setError($error);
    /**
     * @return string
     */
    public function getProductType();

    /**
     * @param string $productType
     * @return $this
     */
    public function setProductType($productType);


    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return \Scriptlodge\Bexio\Api\Data\BexioCustomerInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     *
     * @return \Scriptlodge\Bexio\Api\Data\BexioCustomerInterface
     */
    public function setUpdatedAt($updatedAt);

}
