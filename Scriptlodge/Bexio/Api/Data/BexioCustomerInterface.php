<?php

namespace Scriptlodge\Bexio\Api\Data;

/**
 * Interface BexioCustomerInterface
 *
 * @package Scriptlodge\Bexio\Api\Data
 */
interface BexioCustomerInterface
{
    /**#@+
     * Constants defined for keys of data array
     */

    const ENTITY_ID = 'entity_id';
    const CUSTOMER_ID = 'customer_id';
    const BEXIO_CONTACT_ID = 'bexio_contact_id';
    const COUNTRY_ID = 'country_id';
    const EMAIL = 'email';
    const NEED_SYNC = 'need_sync';
    const ERROR = 'error';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    const TABLE_NAME = 'bexio_customer';

    /**#@-*/

    /**
     * Get id
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set id
     * @param int $entityId
     * @return \Scriptlodge\Bexio\Api\Data\BexioCustomerInterface
     */
    public function setEntityId($entityId);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     *
     * @return \Scriptlodge\Bexio\Api\Data\BexioCustomerInterface
     */
    public function setCustomerId($customerId);

    /**
     * @return string
     */
    public function getBexioContactId();

    /**
     * @param string $bexioContactId
     *
     * @return $this
     */
    public function setBexioContactId($bexioContactId);


    /**
     * @return int
     */
    public function getCountryId();

    /**
     * @param string $countryId
     *
     * @return $this
     */
    public function setCountryId($countryId);

    /**
     * @return int
     */
    public function getNeedSync();

    /**
     * @param int $needSync
     * @return $this
     */
    public function setNeedSync($needSync);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * @return string
     */
    public function getError();

    /**
     * @param string $error
     * @return $this
     */
    public function setError($error);


    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return \Scriptlodge\Bexio\Api\Data\BexioCustomerInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     *
     * @return \Scriptlodge\Bexio\Api\Data\BexioCustomerInterface
     */
    public function setUpdatedAt($updatedAt);

}
