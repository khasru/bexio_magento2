<?php

namespace Scriptlodge\Bexio\Model\ResourceModel;

use Scriptlodge\Bexio\Api\Data\BexioProductInterface;

/**
 * BexioProduct resource
 */
class BexioProduct extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
    private $bexioProductTable = BexioProductInterface::TABLE_NAME;

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
        $this->_init($this->bexioProductTable, BexioProductInterface::ENTITY_ID);
    }
}
