<?php

namespace Scriptlodge\Bexio\Model\ResourceModel;

use Scriptlodge\Bexio\Api\Data\BexioCustomerInterface;

/**
 * BexioCustomer resource
 */
class BexioCustomer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * BexioCustomer Table
     * @var string
     */
    private $bexioCustomerTable = BexioCustomerInterface::TABLE_NAME;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    )
    {
        $this->_date = $date;
        parent::__construct($context);
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init($this->bexioCustomerTable, BexioCustomerInterface::ENTITY_ID);
    }
}
