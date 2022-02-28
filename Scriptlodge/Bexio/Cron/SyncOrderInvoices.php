<?php

namespace Scriptlodge\Bexio\Cron;

use Magento\Sales\Api\OrderRepositoryInterface;

class SyncOrderInvoices
{

    protected $_logger;
    protected $_orderRepository;
    protected $_proceesOrder;


    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Api\SearchCriteriaBuilder            $searchCriteriaBuilder,
        \Magento\Framework\Api\SortOrderBuilder                 $sortOrderBuilder,
        \Scriptlodge\Bexio\Api\BexioOrderRepositoryInterface    $bexioOrderRepositoryInterface,
        \Scriptlodge\Bexio\Model\ResourceModel\BexioOrder\CollectionFactory $collectionFactory,
        OrderRepositoryInterface $orderRepositoryInterface,
        \Scriptlodge\Bexio\Model\ProceesOrder $proceesOrder

    )
    {
        $this->_logger = $logger;
        $this->_orderRepository = $orderRepositoryInterface;
        $this->_proceesOrder = $proceesOrder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder=$sortOrderBuilder;
        $this->_bexioOrderRepositoryInterface = $bexioOrderRepositoryInterface;
        $this->collectionFactory=$collectionFactory;

    }

    /**
     * Sync Order to promail.
     * @return void
     */
    public function execute()
    {

       /*  $orderId=4172;


                $this->_proceesOrder->sendOrderInvoiceToBexio($orderId);*/
        ## send Invoice to bexio ###

        $to = date("Y-m-d h:i:s");
        $from = strtotime('-20 day', strtotime($to));
        $from = date('Y-m-d h:i:s', $from);

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('bexio_invoice', array('null'=>true));
        $collection->addFieldToFilter('created_at', array('gt'=>$from));

        if(!empty($collection->getItems())){
            foreach ($collection->getItems() as $bexioOrder){
                $orderId = $bexioOrder->getOrderId();
                $bexioOrderId = $bexioOrder->getBexioOrderId();
                if ($orderId == '' || $bexioOrderId == '') {
                    continue;
                }else{
                    $this->_proceesOrder->sendOrderInvoiceToBexio($orderId);
                }
            }
        }
/*
        $this->searchCriteriaBuilder->addFilter('bexio_invoice', null, 'eq');
        $this->searchCriteriaBuilder->addFilter('created_at', $from, 'gt');
        $this->searchCriteriaBuilder->setPageSize(50)->setCurrentPage(1);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $orderMap = $this->_bexioOrderRepositoryInterface->getList($searchCriteria);

        if (!empty($orderMap)) {  print_r(count($orderMap->getItems()));
            foreach ($orderMap->getItems() as $bexioOrder) {
                $orderId = $bexioOrder->getOrderId();
              echo  $bexioOrderId = $bexioOrder->getBexioOrderId();
                if ($orderId == '' || $bexioOrderId == '') {
                    continue;
                }
                exit('kkk');
                $this->_proceesOrder->sendOrderInvoiceToBexio($orderId);
            }
        }*/

        ### Issue invoice ###

        $this->_proceesOrder->issueAnInvoiceToBexio();

        ### Manual Entry for online order ###




    }

    public function sendManualEntry()
    {
        $to = date("Y-m-d h:i:s");
        $from = strtotime('-35 day', strtotime($to));
        $from = date('Y-m-d h:i:s', $from);

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('bexio_manual_entry', array('null' => true));
        $collection->addFieldToFilter('created_at', array('gt' => $from));

        //  $orderId = 4177;
        if (!empty($collection)) {

            foreach ($collection->getItems() as $bexioOrder) {
                $orderMapId = $bexioOrder->getEntityId();
                $orderId = $bexioOrder->getOrderId();
                $bexioOrderId = $bexioOrder->getBexioOrderId();
                if ($orderId == '' || $bexioOrderId == '') {
                    continue;
                } else {
                    $this->_proceesOrder->sendOrManualEntryToBexio($orderId, $orderMapId);

                }
            }
        }

    }

}
