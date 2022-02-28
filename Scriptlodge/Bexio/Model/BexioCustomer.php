<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bexio\Model;

use Scriptlodge\Bexio\Api\Data\BexioCustomerInterface;

/**
 * Class BexioCustomer
 *
 * @codeCoverageIgnore
 */
class BexioCustomer extends \Magento\Framework\Model\AbstractModel implements
    BexioCustomerInterface
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Scriptlodge\Bexio\Model\ResourceModel\BexioCustomer::class);
    }


    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->_getData(BexioCustomerInterface::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId)
    {
        $this->setData(BexioCustomerInterface::ENTITY_ID, $entityId);

        return $this;
    }


    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->_getData(BexioCustomerInterface::CUSTOMER_ID);
    }

    /**
     * @param int $customerId
     * @return mixed
     */
    public function setCustomerId($customerId)
    {
        $this->setData(BexioCustomerInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBexioContactId()
    {
        return $this->_getData(BexioCustomerInterface::BEXIO_CONTACT_ID);
    }

    /**
     * @param string $bexioContactId
     * @return mixed
     */
    public function setBexioContactId($bexioContactId)
    {
        $this->setData(BexioCustomerInterface::BEXIO_CONTACT_ID, $bexioContactId);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountryId()
    {
        return $this->_getData(BexioCustomerInterface::COUNTRY_ID);
    }

    /**
     * @param string $countryId
     * @return mixed
     */
    public function setCountryId($countryId)
    {
        $this->setData(BexioCustomerInterface::COUNTRY_ID, $countryId);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNeedSync()
    {
        return $this->_getData(BexioCustomerInterface::NEED_SYNC);
    }

    /**
     * @param int $needSync
     * @return mixed
     */
    public function setNeedSync($needSync)
    {
        $this->setData(BexioCustomerInterface::NEED_SYNC, $needSync);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->_getData(BexioCustomerInterface::EMAIL);
    }

    /**
     * @param string $email
     * @return mixed
     */
    public function setEmail($email)
    {
        $this->setData(BexioCustomerInterface::EMAIL, $email);

        return $this;
    }


    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->_getData(BexioCustomerInterface::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return mixed
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(BexioCustomerInterface::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->_getData(BexioCustomerInterface::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return mixed
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(BexioCustomerInterface::UPDATED_AT, $updatedAt);

        return $this;
    }

    public function getError()
    {
        return $this->_getData(BexioCustomerInterface::ERROR);
    }

    public function setError($error)
    {
        $this->setData(BexioCustomerInterface::ERROR, $error);
        return $this;
    }
}
