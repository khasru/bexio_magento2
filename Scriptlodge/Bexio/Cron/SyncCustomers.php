<?php

namespace Scriptlodge\Bexio\Cron;

use Magento\Sales\Api\OrderRepositoryInterface;

class SyncCustomers
{

    protected $_logger;
    protected $orderRepository;
    protected $proceesOrder;


    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        \Scriptlodge\Bexio\Model\ProceesCustomer $proceesCustomer

    )
    {
        $this->_logger = $logger;
        $this->_orderRepository = $orderRepository;
        $this->proceesCustomer = $proceesCustomer;

    }

    /**
     * Sync Order to promail.
     * @return void
     */
    public function execute()
    {
        $this->proceesCustomer->sendCustomerToBexio(3204);
    }

}
