<?php
/**
 * Yireo DeleteAnyOrder for Magento
 *
 * @package     Yireo_DeleteAnyOrder
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

/**
 * DeleteAnyOrder Order
 */
class Yireo_DeleteAnyOrder_Model_Order
{
    /**
     * @var Mage_Adminhtml_Model_Session
     */
    protected $_session;

    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * Yireo_DeleteAnyOrder_Model_Order constructor.
     */
    public function __construct()
    {
        $this->_session = Mage::getModel('adminhtml/session');
        $this->_order = Mage::getSingleton('sales/order');
    }

    /**
     * Load an order
     *
     * @param int $orderId
     *
     * @return Mage_Sales_Model_Order
     * @throws InvalidArgumentException
     */
    public function load($orderId)
    {
        if (empty($orderId)) {
            throw new InvalidArgumentException;
        }

        // Initialize the order
        $order = $this->_order->load($orderId);

        return $order;
    }

    /**
     * Delete an order entirely
     *
     * @param int $orderId
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function delete($orderId)
    {
        if (empty($orderId)) {
            throw new InvalidArgumentException;
        }

        $this->_order->load($orderId);
        $orderStoreId = $this->_order->getStoreId();
        $orderIncrementId = $this->_order->getIncrementId();

        if (empty($this->_order) || !is_object($this->_order)) {
            $this->_session->addNotice($this->__('Failed to load order with ID %d', $orderId));
            return false;
        }

        // Delete history comments (sales/order_status_history)
        $this->deleteOrderStatusHistory();

        // Delete invoices (sales/order_invoice)
        $this->deleteOrderInvoices();

        // Delete taxes (sales/order_tax)
        $this->deleteOrderTax();

        // Delete payments (sales/order_payment)
        $this->deleteOrderPayments();

        // Delete credit memos (sales/order_creditmemo)
        $this->deleteOrderCreditmemos();

        // Delete tracks (sales/order_shipment_track)
        $this->deleteOrderShipmentTracking();

        // Delete shipments (sales/order_shipment)
        $this->deleteOrderShipments();

        // Delete addresses (sales/order_address)
        $this->deleteOrderAddresses();

        // Delete items individually (sales/order_item)
        $this->deleteOrderItemsFromOrder();

        // Finally delete the order itself
        $this->_delete();

        // Delete any quotes referring to this order 
        $this->deleteQuotesByOrderIncrementId($orderIncrementId);

        // Make sure the grid is fixed anyway
        $this->fixGrids();

        // Restore the original increment IDs
        $this->resetStore($orderStoreId);

        return true;
    }

    /**
     */
    protected function deleteOrderStatusHistory()
    {
        try {
            $collection = $this->getOrderRelatedCollection($this->_order, 'sales/order_status_history_collection');
            $this->deleteCollection($collection);
        } catch (Exception $e) {
            $this->_session->addNotice(
                $this->__('Failed to delete collection %s: %s', 'sales/order_status_history_collection', $e->getMessage())
            );
        };
    }

    /**
     */
    protected function deleteOrderInvoices()
    {
        try {
            $collection = $this->getOrderRelatedCollection($this->_order, 'sales/order_invoice_collection');
            $this->deleteCollection($collection);
        } catch (Exception $e) {
            $this->_session->addNotice(
                $this->__('Failed to delete collection %s: %s', 'sales/order_invoice_collection', $e->getMessage())
            );
        };
    }

    /**
     */
    protected function deleteOrderTax()
    {
        try {
            $collection = Mage::getModel('sales/order_tax')->getCollection()->loadByOrder($this->_order);
            $this->deleteCollection($collection);
        } catch (Exception $e) {
            $this->_session->addNotice(
                $this->__('Failed to delete collection %s: %s', 'sales/order_tax_collection', $e->getMessage())
            );
        };
    }

    /**
     */
    protected function deleteOrderPayments()
    {
        try {
            $collection = $this->getOrderRelatedCollection($this->_order, 'sales/order_payment_collection');
            $this->deleteCollection($collection);
        } catch (Exception $e) {
            $this->_session->addNotice(
                $this->__('Failed to delete collection %s: %s', 'sales/order_payment_collection', $e->getMessage())
            );
        };
    }

    /**
     */
    protected function deleteOrderCreditmemos()
    {
        try {
            $collection = $this->getOrderRelatedCollection($this->_order, 'sales/order_creditmemo_collection');
            $this->deleteCollection($collection);
        } catch (Exception $e) {
            $this->_session->addNotice(
                $this->__('Failed to delete collection %s: %s', 'sales/order_creditmemo_collection', $e->getMessage())
            );
        };
    }

    /**
     */
    protected function deleteOrderShipmentTracking()
    {
        try {
            $collection = $this->getOrderRelatedCollection($this->_order, 'sales/order_shipment_track_collection');
            $this->deleteCollection($collection);
        } catch (Exception $e) {
            $this->_session->addNotice(
                $this->__('Failed to delete collection %s: %s', 'sales/order_shipment_track_collection', $e->getMessage())
            );
        };
    }

    /**
     */
    protected function deleteOrderShipments()
    {
        try {
            $collection = $this->getOrderRelatedCollection($this->_order, 'sales/order_shipment_collection');
            $this->deleteCollection($collection);
        } catch (Exception $e) {
            $this->_session->addNotice(
                $this->__('Failed to delete collection %s: %s', 'sales/order_shipment_collection', $e->getMessage())
            );
        };
    }

    /**
     */
    protected function deleteOrderAddresses()
    {
        try {
            $collection = $this->getOrderRelatedCollection($this->_order, 'sales/order_address_collection');
            $this->deleteCollection($collection);
        } catch (Exception $e) {
            $this->_session->addNotice(
                $this->__('Failed to delete collection %s: %s', 'sales/order_address_collection', $e->getMessage())
            );
        };
    }

    /**
     */
    protected function _delete()
    {
        try {
            $this->_order->delete();
        } catch (Exception $e) {
            $this->_session->addNotice(
                $this->__('Failed to delete order-item: %s', $e->getMessage())
            );
        };
    }

    /**
     *
     * @return boolean
     */
    protected function deleteOrderItemsFromOrder()
    {
        $orderStoreId = $this->_order->getStoreId();

        $orderItemCollection = Mage::getSingleton('sales/order_item')->getCollection();
        $orderItemCollection->addFieldToFilter('order_id', $this->_order->getId());

        if (empty($orderItemCollection)) {
            return false;
        }

        foreach ($orderItemCollection as $orderItem) {
            $this->deleteOrderItem($orderItem, $orderStoreId);
        }
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @param int $orderStoreId
     *
     * @return bool
     */
    protected function deleteOrderItem(Mage_Sales_Model_Order_Item $orderItem, $orderStoreId)
    {
        // Delete downloadable products
        $this->deleteDownloadableLink($orderItem);

        // Optionally reset the stock
        $this->resetStock($orderItem, $orderStoreId);

        // Delete order item itself
        try {
            $orderItem->delete();
        } catch (Exception $e) {
            $this->_session->addNotice(
                $this->__('Failed to delete order item: %s', $e->getMessage())
            );
        };
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     */
    protected function deleteDownloadableLink(Mage_Sales_Model_Order_Item $orderItem)
    {
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        $downloadable = (isset($modules['Mage_Downloadable'])) ? $modules['Mage_Downloadable'] : null;
        if ($downloadable != null && $downloadable['active'] == true) {
            try {
                $linkPurchasedItem = Mage::getModel('downloadable/link_purchased_item');
                if (!empty($linkPurchasedItem) && is_object($linkPurchasedItem)) {
                    $linkPurchasedItem->load($orderItem->getId(), 'order_item_id');
                }

                if (!empty($linkPurchasedItem) && is_object($linkPurchasedItem)) {
                    $linkPurchasedItem->delete();
                }

                $linkPurchased = Mage::getModel('downloadable/link_purchased');
                if (!empty($linkPurchased) && is_object($linkPurchased)) {
                    $linkPurchased->load($orderItem->getId(), 'order_item_id');
                }

                if (!empty($linkPurchased) && is_object($linkPurchased)) {
                    $linkPurchased->delete();
                }
            } catch (Exception $e) {
                $this->_session->addNotice(
                    $this->__('Failed to delete downloadable product: %s', $e->getMessage())
                );
            };
        }
    }

    /**
     * @param $orderIncrementId
     *
     * @throws Exception
     */
    protected function deleteQuotesByOrderIncrementId($orderIncrementId)
    {
        /** @var Mage_Sales_Model_Entity_Quote_Collection $quoteCollection */
        $quoteCollection = Mage::getModel('sales/quote')->getCollection();
        $quoteCollection->addFieldToFilter('reserved_order_id', $orderIncrementId);

        foreach ($quoteCollection as $quote) {
            /** @var Mage_Sales_Model_Quote $quote */
            $quote->delete();
        }
    }

    /**
     * @param $orderId
     */
    protected function fixGrids()
    {
        $orderId = (int)$this->_order->getId();

        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid');
        $query = 'DELETE FROM `' . $table . '` WHERE `entity_id` = ' . $orderId;
        try {
            $db->query($query);
        } catch (Exception $e) {
        }

        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_invoice_grid');
        $query = 'DELETE FROM `' . $table . '` WHERE `order_id` = ' . $orderId;
        try {
            $db->query($query);
        } catch (Exception $e) {
        }
    }

    /**
     * @param $orderStoreId int
     *
     * @return boolean
     */
    protected function resetStore($orderStoreId)
    {
        if (Mage::getStoreConfig('deleteanyorder/settings/resetincrements', $orderStoreId) == 1) {
            return false;
        }

        $this->resetOrderIncrementId($orderStoreId);
        $this->resetInvoiceIncrementId($orderStoreId);
        $this->resetShipmentIncrementId($orderStoreId);
        return true;
    }

    /**
     * Delete an abstract collection by looping throw all its items
     *
     * @param Varien_Data_Collection_Db $collection
     *
     * @return bool
     */
    public function deleteCollection($collection)
    {
        if (!empty($collection)) {
            foreach ($collection as $item) {

                /** @var Mage_Sales_Model_Order_Item $item */

                // Try to delete all the comments (sales/order_***_comment)
                $comments = null;
                try {
                    /** @var Mage_Sales_Model_Mysql4_Order_Comment_Collection_Abstract $comments */
                    $comments = $item->getCommentsCollection();
                } catch (Exception $e) {
                };

                if (!empty($comments)) {
                    $this->deleteCollection($comments);
                }

                // Try to delete all the items (sales/order_***_item)
                $items = null;
                try {
                    $items = $item->getItemsCollection();
                } catch (Exception $e) {
                };

                if (!empty($items)) {
                    $this->deleteCollection($items);
                }

                // Try to delete the item itself
                try {
                    if (is_object($item)) {
                        $item->delete();
                    }
                } catch (Exception $e) {
                };
            }

            return true;
        }
        return false;
    }

    /**
     * Generic method to include a specific collection
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $name
     *
     * @return boolean|Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    protected function getOrderRelatedCollection($order, $name)
    {
        try {
            /** @var Mage_Core_Model_Resource_Db_Collection_Abstract $collection */
            $collection = Mage::getResourceModel($name)
                ->addAttributeToSelect('*')
                ->setOrderFilter($order->getId());
            return $collection;
        } catch (Exception $e) {
        }

        return false;
    }

    /**
     * Optionally reset the available stock
     *
     * @param Mage_Sales_Model_Order_Item $item
     *
     * @return bool
     */
    protected function resetStock($item, $orderStoreId)
    {
        if (Mage::getStoreConfig('deleteanyorder/settings/resetstock', $orderStoreId) !== 1) {
            return false;
        }

        $qty = $item->getQtyOrdered() - $item->getQtyCanceled();
        $children = $item->getChildrenItems();

        if ($item->getId() && ($productId = $item->getProductId()) && empty($children) && $qty) {
            $product = Mage::getModel('catalog/product')->load($productId);
            Mage::getSingleton('cataloginventory/stock')->backItemQty($productId, $qty);
            $this->_session->addNotice($this->__('Reset stock of product "%s" with quantity %d', $product->getName(), $qty));
        }

        return true;
    }

    /**
     * Reset the increment-ID of orders
     *
     * @param int $storeId
     *
     * @return bool
     */
    protected function resetOrderIncrementId($storeId)
    {
        $lastOrder = $this->_order->getCollection()
            ->addFieldToFilter('store_id', $storeId)
            ->addAttributeToSort('increment_id', 'DESC')
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem();

        if (empty($lastOrder)) {
            return false;
        }

        $newIncrementId = $lastOrder->getIncrementId();
        $entityTypeModel = Mage::getSingleton('eav/config')->getEntityType('order');
        $entityStoreConfig = Mage::getModel('eav/entity_store')->loadByEntityStore($entityTypeModel->getId(), $storeId);
        $entityStoreConfig->setIncrementLastId($newIncrementId)->save();

        return true;
    }

    /**
     * Reset the increment-ID of invoices
     *
     * @param int $storeId
     *
     * @return bool
     */
    protected function resetInvoiceIncrementId($storeId)
    {
        $invoiceCollection = Mage::getModel('sales/order_invoice')->getCollection();

        /** @var Mage_Sales_Model_Order_Invoice $lastInvoice */
        $lastInvoice = $invoiceCollection
            ->addFieldToFilter('store_id', $storeId)
            ->addAttributeToSort('increment_id', 'DESC')
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem();

        if (empty($lastInvoice)) {
            return false;
        }

        $newIncrementId = $lastInvoice->getIncrementId();
        $entityTypeModel = Mage::getSingleton('eav/config')->getEntityType('invoice');
        $entityStoreConfig = Mage::getModel('eav/entity_store')->loadByEntityStore($entityTypeModel->getId(), $storeId);
        $entityStoreConfig->setIncrementLastId($newIncrementId);
        try {
            $entityStoreConfig->save();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Translate
     *
     * @return string
     */
    protected function __()
    {
        $args = func_get_args();
        $expr = new Mage_Core_Model_Translate_Expr(array_shift($args), 'Yireo_DeleteAnyOrder');
        array_unshift($args, $expr);
        return Mage::app()->getTranslator()->translate($args);
    }

    /**
     * Reset the increment-ID of shipments
     *
     * @param int $storeId
     *
     * @return bool
     */
    protected function resetShipmentIncrementId($storeId)
    {
        $shipmentCollection = Mage::getModel('sales/order_shipment')->getCollection();

        /** @var Mage_Sales_Model_Order_Shipment $lastShipment */
        $lastShipment = $shipmentCollection
            ->addFieldToFilter('store_id', $storeId)
            ->addAttributeToSort('increment_id', 'DESC')
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem();

        if (empty($lastShipment)) {
            return false;
        }

        $newIncrementId = $lastShipment->getIncrementId();
        $entityTypeModel = Mage::getSingleton('eav/config')->getEntityType('shipment');
        $entityStoreConfig = Mage::getModel('eav/entity_store')->loadByEntityStore($entityTypeModel->getId(), $storeId);
        $entityStoreConfig->setIncrementLastId($newIncrementId);

        try {
            $entityStoreConfig->save();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
