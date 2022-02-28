<?php

namespace Scriptlodge\Bexio\Cron;

use Magento\Catalog\Api\ProductRepositoryInterface;

class SyncProducts
{

    protected $_logger;
    protected $_productRepository;
    protected $_proceesProduct;


    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepositoryInterface,
        \Scriptlodge\Bexio\Model\ProceesProduct $proceesProduct

    )
    {
        $this->_logger = $logger;
        $this->_productRepository = $productRepositoryInterface;
        $this->_proceesProduct = $proceesProduct;

    }

    /**
     * Sync Order to promail.
     * @return void
     */
    public function execute()
    {
       /* $this->_proceesProduct->getArticleBySku('12433');
        exit();*/
       $productMap= $this->_proceesProduct->getProductMapData();
       if(count($productMap->getItems())>0){
           foreach ($productMap->getItems() as $item){
               $this->_proceesProduct->sendProductToBexio($item->getSku());

           }
       }

    }

}
