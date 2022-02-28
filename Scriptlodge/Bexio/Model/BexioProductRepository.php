<?php

namespace Scriptlodge\Bexio\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Scriptlodge\Bexio\Api\BexioProductRepositoryInterface;
use Scriptlodge\Bexio\Api\Data\BexioProductInterface;
use Scriptlodge\Bexio\Model\BexioProductFactory as BexioProductFactory;
use Scriptlodge\Bexio\Model\ResourceModel\BexioProduct as ResourceBexioProduct;
use Scriptlodge\Bexio\Model\ResourceModel\BexioProduct\CollectionFactory as CollectionFactory;


class BexioProductRepository implements BexioProductRepositoryInterface
{

    /**
     * @var BexioProductInterface[]
     */
    private $instances = [];

    /**
     * @var \Scriptlodge\Bexio\Model\ResourceModel\BexioProduct
     */
    protected $resourceBexioProduct;


    /**
     * @var \Scriptlodge\Bexio\Model\BexioProductFactory
     */
    protected $bexioProductFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;


    /**
     * BexioProductRepository constructor.
     * @param ResourceBexioProduct $resourceBexioProduct
     * @param BexioProductInterfaceFactory $bexioProductInterfaceFactory
     * @param CollectionFactory $collectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param BexioProductFactory $bexioProductFactory
     * @param \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     */


    public function __construct(
        ResourceBexioProduct                                               $resourceBexioProduct,
        CollectionFactory                                                  $collectionFactory,
        DataObjectHelper                                                   $dataObjectHelper,
        BexioProductFactory                                                $bexioProductFactory,
        \Magento\Framework\Api\SearchResultsInterfaceFactory               $searchResultsFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface   $extensionAttributesJoinProcessor,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
    )
    {
        $this->resourceBexioProduct = $resourceBexioProduct;
        $this->collectionFactory = $collectionFactory;
        $this->BexioProductFactory = $bexioProductFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
    }


    /**
     * {@inheritdoc}
     */
    public function save(\Scriptlodge\Bexio\Api\Data\BexioProductInterface $bexioProduct)
    {
        try {
            $this->resourceBexioProduct->save($bexioProduct);
            return $bexioProduct;
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Bexio Product: %1',
                $exception->getMessage()
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(\Scriptlodge\Bexio\Api\Data\BexioProductInterface $bexioProduct)
    {
        try {
            $this->resourceBexioProduct->save($bexioProduct);
            return $bexioProduct;
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Bexio product: %1',
                $exception->getMessage()
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($bexioMapId)
    {

        if (!isset($this->instances[$bexioMapId])) {
            $bexioProduct = $this->bexioProductFactory->create();
            $bexioProduct->load($bexioMapId);
            if (!$bexioProduct->getId()) {
                throw NoSuchEntityException::singleField('id', $bexioMapId);
            }
            $this->instances[$bexioMapId] = $bexioProduct;
        }
        return $this->instances[$bexioMapId];
    }

    public function getByProductId($productId)
    {
        /** @var SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(BexioProductInterface::PRODUCT_ID, $productId);

        if (empty($collection->getItems())) {
            return $collection->getItems();
            //  throw new NoSuchEntityException(__('Bexio product Items with customer ID "%1" does not exist.', $customerId));
        }
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    public function getBySku($sku)
    {
        /** @var SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(BexioProductInterface::SKU, $sku);

        if (empty($collection->getItems())) {
            return $collection->getItems();
            //  throw new NoSuchEntityException(__('Bexio product Items with customer ID "%1" does not exist.', $customerId));
        }
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        //   $this->extensionAttributesJoinProcessor->process($collection);

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }


}


