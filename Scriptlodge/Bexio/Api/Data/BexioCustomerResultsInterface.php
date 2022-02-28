<?php

namespace Scriptlodge\Bexio\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface BexioCustomerResultsInterface extends SearchResultsInterface
{

    /**
     * Get BexioCustomer list.
     * @return \Scriptlodge\Bexio\Api\Data\BexioCustomerInterface[]
     */
    public function getItems();

    /**
     * Set BexioCustomer list.
     * @param \Scriptlodge\Bexio\Api\Data\BexioCustomerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
