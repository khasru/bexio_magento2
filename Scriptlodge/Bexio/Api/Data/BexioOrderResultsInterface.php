<?php

namespace Scriptlodge\Bexio\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface BexioOrderResultsInterface extends SearchResultsInterface
{

    /**
     * Get BexioOrder list.
     * @return \Scriptlodge\Bexio\Api\Data\BexioOrderInterface[]
     */
    public function getItems();

    /**
     * Set BexioOrder list.
     * @param \Scriptlodge\Bexio\Api\Data\BexioOrderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
