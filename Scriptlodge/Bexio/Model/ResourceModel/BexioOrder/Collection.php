<?php

namespace Scriptlodge\Bexio\Model\ResourceModel\BexioOrder;


/**
 * BexioOrder Collection
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
        $this->_init('Scriptlodge\Bexio\Model\BexioOrder', 'Scriptlodge\Bexio\Model\ResourceModel\BexioOrder');
    }
}
