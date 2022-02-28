<?php

namespace Scriptlodge\Bexio\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerSaveAfterObserver implements ObserverInterface
{
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Scriptlodge\Bexio\Model\ProceesCustomer $proceesCustomer

    )
    {
        $this->_logger = $logger;
        $this->proceesCustomer = $proceesCustomer;

    }


    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerId=$observer->getEvent()->getData('customer')->getId();
        if($customerId) {
            $this->proceesCustomer->sendCustomerToBexio($customerId);
        }
    }
}
