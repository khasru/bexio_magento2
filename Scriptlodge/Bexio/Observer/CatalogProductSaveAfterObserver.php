<?php

namespace Scriptlodge\Bexio\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CatalogProductSaveAfterObserver implements ObserverInterface
{
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Scriptlodge\Bexio\Model\ProceesProduct $proceesProduct
    )
    {
        $this->_logger = $logger;
        $this->proceesProduct = $proceesProduct;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getProduct();
        $this->proceesProduct->updateProductMapStatus($product);
       // exit();
    }
}
