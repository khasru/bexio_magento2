<?php

namespace Scriptlodge\Bexio\Api;

use Magento\Framework\Exception\LocalizedException;

use Scriptlodge\Bexio\Api\Data\BexioProductInterface;

/**
 * Interface BexioProductRepositoryInterface
 *
 * @package Scriptlodge\Bexio\Api
 */
interface BexioProductRepositoryInterface
{

    /**
     * Save BexioProductRepositoryInterface
     * @param BexioProductInterface $bexioProductInterface
     * @return BexioProductInterface
     * @throws LocalizedException
     */
    public function save(\Scriptlodge\Bexio\Api\Data\BexioProductInterface $bexioProductInterface);

    /**
     * Save BexioProductRepositoryInterface
     * @param BexioProductInterface $bexioProductInterface
     * @return BexioProductInterface
     * @throws LocalizedException
     */
    public function update(\Scriptlodge\Bexio\Api\Data\BexioProductInterface $bexioProductInterface);

    /**
     * Retrieve BexioProductInterface data
     * @param int $entityId
     * @return BexioProductInterface
     * @throws LocalizedException
     */
    public function get($entityId);

    /**
     * Retrieve BexioProduct data
     * @param int $productId
     * @return BexioProductInterface
     * @throws LocalizedException
     */
    public function getByProductId($productId);

    /**
     * Retrieve BexioProduct data
     * @param string $sku
     * @return BexioProductInterface
     * @throws LocalizedException
     */
    public function getBySku($sku);

    /**
     * Retrieve $bexioProduct matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Scriptlodge\Bexio\Api\Data\BexioProductResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

}
