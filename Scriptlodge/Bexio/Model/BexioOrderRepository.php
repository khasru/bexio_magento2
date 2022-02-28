<?php

namespace Scriptlodge\Bexio\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Scriptlodge\Bexio\Api\BexioOrderRepositoryInterface;
use Scriptlodge\Bexio\Api\Data\BexioOrderInterface;
use Scriptlodge\Bexio\Model\BexioOrderFactory as BexioOrderFactory;
use Scriptlodge\Bexio\Model\ResourceModel\BexioOrder as ResourceBexioOrder;
use Scriptlodge\Bexio\Model\ResourceModel\BexioOrder\CollectionFactory as CollectionFactory;


class BexioOrderRepository implements BexioOrderRepositoryInterface
{

    /**
     * @var BexioOrderInterface[]
     */
    private $instances = [];

    /**
     * @var \Scriptlodge\Bexio\Model\ResourceModel\BexioOrder
     */
    protected $resourceBexioOrder;


    /**
     * @var \Scriptlodge\Bexio\Model\BexioOrderFactory
     */
    protected $bexioOrderFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;


    /**
     * BexioOrderRepository constructor.
     * @param ResourceBexioOrder $resourceBexioOrder
     * @param BexioOrderInterfaceFactory $bexioOrderInterfaceFactory
     * @param CollectionFactory $collectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param BexioOrderFactory $bexioCustomerFactory
     * @param \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     */


    public function __construct(
        ResourceBexioOrder                                                 $resourceBexioOrder,
        CollectionFactory                                                  $collectionFactory,
        DataObjectHelper                                                   $dataObjectHelper,
        BexioOrderFactory                                                  $bexioOrderFactory,
        \Magento\Framework\Api\SearchResultsInterfaceFactory               $searchResultsFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface   $extensionAttributesJoinProcessor,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
    )
    {
        $this->resourceBexioOrder = $resourceBexioOrder;
        $this->collectionFactory = $collectionFactory;
        $this->bexioOrderFactory = $bexioOrderFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
    }


    /**
     * {@inheritdoc}
     */
    public function save(\Scriptlodge\Bexio\Api\Data\BexioOrderInterface $bexioOrder)
    {
        try {
            $this->resourceBexioOrder->save($bexioOrder);
            return $bexioOrder;
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Bexio Order: %1',
                $exception->getMessage()
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(\Scriptlodge\Bexio\Api\Data\BexioOrderInterface $bexioOrder)
    {
        try {
            $this->resourceBexioOrder->save($bexioOrder);
            return $bexioOrder;
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Bexio Order: %1',
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
            $bexioOrder = $this->bexioOrderFactory->create();
            $bexioOrder->load($bexioMapId);
            if (!$bexioOrder->getId()) {
                throw NoSuchEntityException::singleField('id', $bexioMapId);
            }
            $this->instances[$bexioMapId] = $bexioOrder;
        }
        return $this->instances[$bexioMapId];
    }

    /**
     * {@inheritdoc}
     */

    public function getByOrderId($orderId)
    {
        /** @var SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(BexioOrderInterface::ORDER_ID, $orderId);

        if (empty($collection->getItems())) {
            return $collection->getItems();
            //  throw new NoSuchEntityException(__('Bexio order Items with order ID "%1" does not exist.', $customerId));
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


