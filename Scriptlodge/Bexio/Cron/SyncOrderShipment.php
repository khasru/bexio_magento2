<?php

namespace Scriptlodge\Bexio\Cron;

use Magento\Sales\Api\OrderRepositoryInterface;
use Scriptlodge\Bexio\Api\Data\BexioOrderInterface;

class SyncOrderShipment
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
        ## send Delivery to bexio ###

        $to = date("Y-m-d h:i:s");
        $from = strtotime('-20 day', strtotime($to));
        $from = date('Y-m-d h:i:s', $from);



        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(BexioOrderInterface::BEXIO_SHIPMENT, array('null'=>true));
        $collection->addFieldToFilter(BexioOrderInterface::CREATED_AT, array('gt'=>$from));

        if(!empty($collection)){

            foreach ($collection->getItems() as $bexioOrder){
                $orderId = $bexioOrder->getOrderId();
                if($orderId<='15694') continue;
                $bexioOrderId = $bexioOrder->getBexioOrderId();
                if ($orderId == '' || $bexioOrderId == '') {
                    continue;
                }else{
                    $this->_proceesOrder->sendOrDerdeliveryToBexio($orderId);
                }
            }
        }


        ### Issue Delivery ###

        $this->_proceesOrder->issueDeliveryToBexio();

    }

}
