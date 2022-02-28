<?php

namespace Scriptlodge\Bexio\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface BexioProductResultsInterface extends SearchResultsInterface
{

    /**
     * Get BexioProduct list.
     * @return \Scriptlodge\Bexio\Api\Data\BexioProductInterface[]
     */
    public function getItems();

    /**
     * Set BexioProduct list.
     * @param \Scriptlodge\Bexio\Api\Data\BexioProductInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
