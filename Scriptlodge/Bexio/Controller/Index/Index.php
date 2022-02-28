<?php

namespace Scriptlodge\Bexio\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Index extends Action
{
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                    $context,
        OrderRepositoryInterface                   $orderRepository,
        CustomerRepositoryInterface                $customerRepository,
        \Scriptlodge\Bexio\Cron\SyncCustomers      $syncCustomers,
        \Scriptlodge\Bexio\Cron\SyncProducts      $syncProducts,
        \Scriptlodge\Bexio\Cron\SyncOrders          $syncOrders,
        \Scriptlodge\Bexio\Cron\SyncOrderInvoices   $syncOrderInvoices,
        \Scriptlodge\Bexio\Cron\SyncOrderShipment   $syncOrderShipment,
        \Magento\Framework\UrlInterface            $urlInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PageFactory                                $resultPageFactory
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_orderRepository = $orderRepository;
        $this->_customerRepository = $customerRepository;
        $this->syncCustomers = $syncCustomers;
        $this->syncProducts = $syncProducts;
        $this->syncOrders = $syncOrders;
        $this->syncOrderInvoices=$syncOrderInvoices;
        $this->syncOrderShipment=$syncOrderShipment;
        $this->_urlInterface = $urlInterface;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        //custome
      //  $this->syncCustomers->execute();
        //product
        //$this->syncProducts->execute();

        // Order
      //  $this->syncOrders->execute();
    //   $this->syncOrderInvoices->execute();
     //  $this->syncOrderShipment->execute();

    }

}
