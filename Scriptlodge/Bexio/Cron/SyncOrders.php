<?php

namespace Scriptlodge\Bexio\Cron;

use Magento\Sales\Api\OrderRepositoryInterface;

class SyncOrders
{

    protected $_logger;
    protected $_orderRepository;
    protected $_proceesOrder;


    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Api\SearchCriteriaBuilder            $searchCriteriaBuilder,
        \Magento\Framework\Api\SortOrderBuilder                 $sortOrderBuilder,
        OrderRepositoryInterface $orderRepositoryInterface,
        \Scriptlodge\Bexio\Model\ProceesOrder $proceesOrder

    )
    {
        $this->_logger = $logger;
        $this->_orderRepository = $orderRepositoryInterface;
        $this->_proceesOrder = $proceesOrder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder=$sortOrderBuilder;

    }

    /**
     * Sync Order to promail.
     * @return void
     */
    public function execute()
    {
      /* $orderId=12547;
        $this->_proceesOrder->sendOrderToBexio($orderId);
exit('sync');*/

        $to = date("Y-m-d h:i:s");
        $from = strtotime('-5 day', strtotime($to));
        $from = date('Y-m-d h:i:s', $from);

        $this->searchCriteriaBuilder->addFilter('status', array('canceled', 'closed'), 'nin');
        $this->searchCriteriaBuilder->addFilter('created_at', $from, 'gt');
        $this->searchCriteriaBuilder->addFilter('store_id', array(1,2,3), 'in');
        $this->searchCriteriaBuilder->setPageSize(800)->setCurrentPage(1);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $_orderItems = $this->_orderRepository->getList($searchCriteria)->getItems();

        foreach ($_orderItems as $order) {
            $orderId = $order->getId();
            $orderMap = $this->_proceesOrder->getOrderMapDataById($orderId);
            if (!empty($orderMap)) {
                $bexioOrderId = $orderMapId = "";
                foreach ($orderMap->getItems() as $item) {
                    $orderMapId = $item->getId();
                    $bexioOrderId = $item->getBexioOrderId();
                }
                if (empty($bexioOrderId)) {
                    $this->_proceesOrder->sendOrderToBexio($orderId);
                } else {
                    continue;
                }
            } else {
                $this->_proceesOrder->sendOrderToBexio($orderId);
            }
        }
    }

}
