<?php

namespace Scriptlodge\Bexio\Api\Data;

/**
 * Interface BexioOrderInterface
 *
 * @package Scriptlodge\Bexio\Api\Data
 */
interface BexioOrderInterface
{
    /**#@+
     * Constants defined for keys of data array
     */

    const ENTITY_ID = 'entity_id';
    const ORDER_ID = 'order_id';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const BEXIO_ORDER_ID = 'bexio_order_id';
    const BEXIO_INVOICE = 'bexio_invoice';
    const BEXIO_SHIPMENT = 'bexio_shipment';
    const BEXIO_INVOICE_SEND = 'bexio_invoice_send';
    const BEXIO_ORDER_POSITINS = 'order_positions';
    const BEXIO_SHIPMENT_DONE = 'bexio_shipment_done';
    const NEED_SYNC = 'need_sync';
    const ERROR = 'error';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    const TABLE_NAME = 'bexio_order';

    /**#@-*/

    /**
     * Get id
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set id
     * @param int $entityId
     * @return \Scriptlodge\Bexio\Api\Data\BexioOrderInterface
     */
    public function setEntityId($entityId);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $orderId
     *
     * @return \Scriptlodge\Bexio\Api\Data\BexioOrderInterface
     */
    public function setOrderId($orderId);

    /**
     * @return string
     */
    public function getOrderIncrementId();

    /**
     * @param string $orderIncrementId
     *
     * @return $this
     */
    public function setOrderIncrementId($orderIncrementId);


    /**
     * @return int
     */
    public function getBexioOrderId();

    /**
     * @param int $bexioOrderId
     *
     * @return $this
     */
    public function setBexioOrderId($bexioOrderId);


    /**
     * @return string
     */
    public function getBexioInvoice();

    /**
     * @param string $bexioInvoice
     *
     * @return $this
     */
    public function setBexioInvoice($bexioInvoice);


    /**
     * @return string
     */
    public function getBexioShipment();

    /**
     * @param string $bexioShipment
     *
     * @return $this
     */
    public function setBexioShipment($bexioShipment);



    /**
     * @return string
     */
    public function getBexioOrderPositions();

    /**
     * @param string $bexioOrderPositions
     *
     * @return $this
     */
    public function setBexioOrderPositions($bexioOrderPositions);


    /**
     * @return int
     */
    public function getBexioInvoiceSend();

    /**
     * @param int $bexioInvoiceSend
     *
     * @return $this
     */
    public function setBexioInvoiceSend($bexioInvoiceSend);


    /**
     * @return int
     */
    public function getBexioShipmentDone();

    /**
     * @param int $bexioShipmentDone
     *
     * @return $this
     */
    public function setBexioShipmentDone($bexioShipmentDone);


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
     * @return \Scriptlodge\Bexio\Api\Data\BexioOrderInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     *
     * @return \Scriptlodge\Bexio\Api\Data\BexioOrderInterface
     */
    public function setUpdatedAt($updatedAt);

}
