<?php

namespace Scriptlodge\Bexio\Model\ResourceModel\BexioProduct;


/**
 * BexioProduct Collection
 *
 * @author  Magento Core Team <khasru96@gamail.com>
 */

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Scriptlodge\Bexio\Model\BexioProduct', 'Scriptlodge\Bexio\Model\ResourceModel\BexioProduct');
    }
}
