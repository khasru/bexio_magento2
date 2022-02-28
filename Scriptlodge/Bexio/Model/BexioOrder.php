<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bexio\Model;

use Scriptlodge\Bexio\Api\Data\BexioOrderInterface;

/**
 * Class BexioOrder
 *
 * @codeCoverageIgnore
 */
class BexioOrder extends \Magento\Framework\Model\AbstractModel implements
    BexioOrderInterface
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Scriptlodge\Bexio\Model\ResourceModel\BexioOrder::class);
    }


    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->_getData(BexioOrderInterface::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId)
    {
        $this->setData(BexioOrderInterface::ENTITY_ID, $entityId);

        return $this;
    }


    /**
     * @return mixed
     */
    public function getNeedSync()
    {
        return $this->_getData(BexioOrderInterface::NEED_SYNC);
    }

    /**
     * @param int $needSync
     * @return mixed
     */
    public function setNeedSync($needSync)
    {
        $this->setData(BexioOrderInterface::NEED_SYNC, $needSync);

        return $this;
    }


    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->_getData(BexioOrderInterface::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return mixed
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(BexioOrderInterface::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->_getData(BexioOrderInterface::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return mixed
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(BexioOrderInterface::UPDATED_AT, $updatedAt);

        return $this;
    }

    public function getError()
    {
        return $this->_getData(BexioOrderInterface::ERROR);
    }

    public function setError($error)
    {
        $this->setData(BexioOrderInterface::ERROR, $error);

        return $this;
    }

    public function getOrderId()
    {
        return $this->_getData(BexioOrderInterface::ORDER_ID);
    }

    public function setOrderId($orderId)
    {
        $this->setData(BexioOrderInterface::ORDER_ID, $orderId);

        return $this;
    }

    public function getOrderIncrementId()
    {
        return $this->_getData(BexioOrderInterface::ORDER_INCREMENT_ID);
    }

    public function setOrderIncrementId($orderIncrementId)
    {
        $this->setData(BexioOrderInterface::ORDER_INCREMENT_ID, $orderIncrementId);

        return $this;
    }

    public function getBexioOrderId()
    {
        return $this->_getData(BexioOrderInterface::BEXIO_ORDER_ID);
    }

    public function setBexioOrderId($bexioOrderId)
    {
        $this->setData(BexioOrderInterface::BEXIO_ORDER_ID, $bexioOrderId);

        return $this;
    }

    public function getBexioInvoice()
    {
        return $this->_getData(BexioOrderInterface::BEXIO_INVOICE);
    }

    public function setBexioInvoice($bexioInvoice)
    {
        $this->setData(BexioOrderInterface::BEXIO_INVOICE, $bexioInvoice);

        return $this;
    }

    public function getBexioShipment()
    {
        return $this->_getData(BexioOrderInterface::BEXIO_SHIPMENT);
    }

    public function setBexioShipment($bexioShipment)
    {
        $this->setData(BexioOrderInterface::BEXIO_SHIPMENT, $bexioShipment);

        return $this;
    }

    public function getBexioInvoiceSend()
    {
        return $this->_getData(BexioOrderInterface::BEXIO_INVOICE_SEND);
    }

    public function setBexioInvoiceSend($bexioInvoiceSend)
    {
        $this->setData(BexioOrderInterface::BEXIO_INVOICE_SEND, $bexioInvoiceSend);

        return $this;
    }

    public function getBexioShipmentDone()
    {
        return $this->_getData(BexioOrderInterface::BEXIO_SHIPMENT_DONE);
    }

    public function setBexioShipmentDone($bexioShipmentDone)
    {
        $this->setData(BexioOrderInterface::BEXIO_SHIPMENT_DONE, $bexioShipmentDone);

        return $this;
    }

    public function getBexioOrderPositions()
    {
        return $this->_getData(BexioOrderInterface::BEXIO_ORDER_POSITINS);
    }

    public function setBexioOrderPositions($bexioOrderPositions)
    {
        $this->setData(BexioOrderInterface::BEXIO_ORDER_POSITINS, $bexioOrderPositions);

        return $this;
    }
}
