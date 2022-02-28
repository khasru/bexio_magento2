<?php

namespace Scriptlodge\Bexio\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Scriptlodge\Bexio\Api\BexioCustomerRepositoryInterface;
use Scriptlodge\Bexio\Api\Data\BexioCustomerInterface;
use Scriptlodge\Bexio\Model\BexioCustomerFactory as BexioCustomerFactory;
use Scriptlodge\Bexio\Model\ResourceModel\BexioCustomer as ResourceBexioCustomer;
use Scriptlodge\Bexio\Model\ResourceModel\BexioCustomer\CollectionFactory as CollectionFactory;


class BexioCustomerRepository implements BexioCustomerRepositoryInterface
{

    /**
     * @var BexioCustomerInterface[]
     */
    private $instances = [];

    /**
     * @var \Scriptlodge\Bexio\Model\ResourceModel\BexioCustomer
     */
    protected $resourceBexioCustomer;


    /**
     * @var \Scriptlodge\Bexio\Model\BexioCustomerFactory
     */
    protected $bexioCustomerFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;


    /**
     * BexioCustomerRepository constructor.
     * @param ResourceBexioCustomer $resourceBexioCustomer
     * @param BexioCustomerInterfaceFactory $bexioCustomerInterfaceFactory
     * @param CollectionFactory $collectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param BexioCustomerFactory $bexioCustomerFactory
     * @param \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     */


    public function __construct(
        ResourceBexioCustomer                                              $resourceBexioCustomer,
        CollectionFactory                                                  $collectionFactory,
        DataObjectHelper                                                   $dataObjectHelper,
        BexioCustomerFactory                                               $bexioCustomerFactory,
        \Magento\Framework\Api\SearchResultsInterfaceFactory               $searchResultsFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface   $extensionAttributesJoinProcessor,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
    )
    {
        $this->resourceBexioCustomer = $resourceBexioCustomer;
        $this->collectionFactory = $collectionFactory;
        $this->bexioCustomerFactory = $bexioCustomerFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
    }


    /**
     * {@inheritdoc}
     */
    public function save(\Scriptlodge\Bexio\Api\Data\BexioCustomerInterface $bexioCustomer)
    {
        try {
            $this->resourceBexioCustomer->save($bexioCustomer);
            return $bexioCustomer;
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Bexio Customer: %1',
                $exception->getMessage()
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(\Scriptlodge\Bexio\Api\Data\BexioCustomerInterface $bexioCustomer)
    {
        try {
            $this->resourceBexioCustomer->save($bexioCustomer);
            return $bexioCustomer;
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Bexio Customer: %1',
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
            $bexioCustomer = $this->bexioCustomerFactory->create();
            $bexioCustomer->load($bexioMapId);
            if (!$bexioCustomer->getId()) {
                throw NoSuchEntityException::singleField('id', $bexioMapId);
            }
            $this->instances[$bexioMapId] = $bexioCustomer;
        }
        return $this->instances[$bexioMapId];
    }

    /**
     * {@inheritdoc}
     */

    public function getByCustomerId($customerId)
    {
        /** @var SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(BexioCustomerInterface::CUSTOMER_ID, $customerId);

        if (empty($collection->getItems())) {
            return $collection->getItems();
            //  throw new NoSuchEntityException(__('Bexio customer Items with customer ID "%1" does not exist.', $customerId));
        }
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }


    /**
     * Delete BexioCustomer
     * @param \Scriptlodge\Bexio\Api\Data\BexioCustomerInterface $bexioCustomer
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Scriptlodge\Bexio\Api\Data\BexioCustomerInterface $bexioCustomer)
    {
        try {
            $bexioCustomerId = $bexioCustomer->getId();
            $this->resourceBexioCustomer->delete($bexioCustomer);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the %2 bexio customer: %1',
                $exception->getMessage(), $bexioCustomer->getId()
            ));
        }
        unset($this->instances[$bexioCustomer->getId()]);
        return true;
    }

    /**
     * Delete BexioCustomer by ID
     * @param string $bexioCustomerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($bexioCustomerId)
    {
        return $this->delete($this->get($bexioCustomerId));
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


