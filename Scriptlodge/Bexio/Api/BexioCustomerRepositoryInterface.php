<?php

namespace Scriptlodge\Bexio\Api;

use Magento\Framework\Exception\LocalizedException;

use Scriptlodge\Bexio\Api\Data\BexioCustomerInterface;

/**
 * Interface BexioCustomerRepositoryInterface'
 *
 * @package Scriptlodge\Bexio\Api
 */
interface BexioCustomerRepositoryInterface
{

    /**
     * Save BexioCustomerInterface
     * @param BexioCustomerInterface $bexioCustomerInterface
     * @return BexioCustomerInterface
     * @throws LocalizedException
     */
    public function save(\Scriptlodge\Bexio\Api\Data\BexioCustomerInterface $bexioCustomerInterface);

    /**
     * Save BexioCustomerRepositoryInterface
     * @param BexioCustomerInterface $bexioCustomerInterface
     * @return BexioCustomerInterface
     * @throws LocalizedException
     */
    public function update(\Scriptlodge\Bexio\Api\Data\BexioCustomerInterface $bexioCustomerInterface);

    /**
     * Retrieve $bexioCustomerInterface data
     * @param int $bexioEntityId
     * @return BexioCustomerInterface
     * @throws LocalizedException
     */
    public function get($bexioEntityId);

    /**
     * Retrieve BexioCustomer data
     * @param int $customerId
     * @return BexioCustomerInterface
     * @throws LocalizedException
     */
    public function getByCustomerId($customerId);


    /**
     * Retrieve $bexioCustomer matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Scriptlodge\Bexio\Api\Data\BexioCustomerResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);


    /**
     * Delete BexioCustomer Data
     * @param BexioCustomerInterface $bexioCustomerInterface
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(BexioCustomerInterface $bexioCustomerInterface);

    /**
     * Delete BexioCustomer data by Id
     * @param string $bexioCustomerId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($bexioCustomerId);

}
