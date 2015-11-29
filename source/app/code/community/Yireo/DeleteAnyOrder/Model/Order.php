<?php
/**
 * Yireo DeleteAnyOrder for Magento 
 *
 * @package     Yireo_DeleteAnyOrder
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (http://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

/**
 * DeleteAnyOrder Order
 */
class Yireo_DeleteAnyOrder_Model_Order
{
    /**
     * Load an order
     *
     * @param int $orderId
     * @return bool
     */
    public function load($orderId)
    {
        // Initialize the order (sales/order)
        $order = Mage::getSingleton('sales/order')->load($orderId);
        return $order;
    }

    /**
     * Delete an order entirely
     *
     * @param int $orderId
     * @return bool
     */
    public function delete($orderId)
    {
        // Initialize the order (sales/order)
        $order = Mage::getSingleton('sales/order')->load($orderId);
        $orderStoreId = $order->getStoreId();
        $orderIncrementId = $order->getIncrementId();

        if(empty($order) || !is_object($order)) {
            Mage::getModel('adminhtml/session')->addNotice(Mage::helper('core')->__('Failed to load order with ID %d', $orderId ));
            return false;
        }

        // Delete history comments (sales/order_status_history)
        try {
            $collection = $this->getOrderRelatedCollection($order, 'sales/order_status_history_collection');
            $this->deleteCollection($collection);
        } catch(Exception $e) {
            Mage::getModel('adminhtml/session')->addNotice(
                Mage::helper('core')->__('Failed to delete collection %s: %s', 'sales/order_status_history_collection', $e->getMsg())
            );
        };

        // Delete invoices (sales/order_invoice)
        try {
            $collection = $this->getOrderRelatedCollection($order, 'sales/order_invoice_collection');
            $this->deleteCollection($collection);
        } catch(Exception $e) {
            Mage::getModel('adminhtml/session')->addNotice(
                Mage::helper('core')->__('Failed to delete collection %s: %s', 'sales/order_invoice_collection', $e->getMsg())
            );
        };

        // Delete taxes (sales/order_tax)
        try {
            $collection = Mage::getModel('sales/order_tax')->getCollection()->loadByOrder($order);
            $this->deleteCollection($collection);
        } catch(Exception $e) {
            Mage::getModel('adminhtml/session')->addNotice(
                Mage::helper('core')->__('Failed to delete collection %s: %s', 'sales/order_tax_collection', $e->getMsg())
            );
        };

        // Delete payments (sales/order_payment)
        try {
            $collection = $this->getOrderRelatedCollection($order, 'sales/order_payment_collection');
            $this->deleteCollection($collection);
        } catch(Exception $e) {
            Mage::getModel('adminhtml/session')->addNotice(
                Mage::helper('core')->__('Failed to delete collection %s: %s', 'sales/order_payment_collection', $e->getMsg())
            );
        };

        // Delete credit memos (sales/order_creditmemo)
        try {
            $collection = $this->getOrderRelatedCollection($order, 'sales/order_creditmemo_collection');
            $this->deleteCollection($collection);
        } catch(Exception $e) {
            Mage::getModel('adminhtml/session')->addNotice(
                Mage::helper('core')->__('Failed to delete collection %s: %s', 'sales/order_creditmemo_collection', $e->getMsg())
            );
        };

        // Delete tracks (sales/order_shipment_track)
        try {
            $collection = $this->getOrderRelatedCollection($order, 'sales/order_shipment_track_collection');
            $this->deleteCollection($collection);
        } catch(Exception $e) {
            Mage::getModel('adminhtml/session')->addNotice(
                Mage::helper('core')->__('Failed to delete collection %s: %s', 'sales/order_shipment_track_collection', $e->getMsg())
            );
        };

        // Delete shipments (sales/order_shipment)
        try {
            $collection = $this->getOrderRelatedCollection($order, 'sales/order_shipment_collection');
            $this->deleteCollection($collection);
        } catch(Exception $e) {
            Mage::getModel('adminhtml/session')->addNotice(
                Mage::helper('core')->__('Failed to delete collection %s: %s', 'sales/order_shipment_collection', $e->getMsg())
            );
        };

        // Delete addresses (sales/order_address)
        try {
            $collection = $this->getOrderRelatedCollection($order, 'sales/order_address_collection');
            $this->deleteCollection($collection);
        } catch(Exception $e) {
            Mage::getModel('adminhtml/session')->addNotice(
                Mage::helper('core')->__('Failed to delete collection %s: %s', 'sales/order_address_collection', $e->getMsg())
            );
        };

        // Delete items individually (sales/order_item)
        $item_collection = Mage::getSingleton('sales/order_item')->getCollection()
            ->addFieldToFilter('order_id', $order->getId());
        if(!empty($item_collection)) {
            foreach($item_collection as $item) {

                // If this entry is corrupt, skip it
                if(empty($item) || !is_object($item)) continue;

                // Delete downloadable products
                $modules = (array)Mage::getConfig()->getNode('modules')->children();   
                $downloadable = (isset($modules['Mage_Downloadable'])) ? $modules['Mage_Downloadable'] : null;
                if($downloadable != null && $downloadable['active'] == true) {
                    try {
                        $object = Mage::getModel('downloadable/link_purchased_item');
                        if(!empty($object) && is_object($object)) $object->load($item->getId(), 'order_item_id');
                        if(!empty($object) && is_object($object)) $object->delete();

                        $object = Mage::getModel('downloadable/link_purchased');
                        if(!empty($object) && is_object($object)) $object->load($item->getId(), 'order_item_id');
                        if(!empty($object) && is_object($object)) $object->delete();
                    } catch(Exception $e) {
                        Mage::getModel('adminhtml/session')->addNotice(
                            Mage::helper('core')->__('Failed to delete downloadable product: %s', $e->getMsg())
                        );
                    };
                }

                // Optionally reset the stock
                if(Mage::getStoreConfig('deleteanyorder/settings/resetstock', $orderStoreId) == 1) {
                    $this->resetStock($item);
                }

                // Delete order item itself
                try {
                    $item->delete();
                } catch(Exception $e) {
                    Mage::getModel('adminhtml/session')->addNotice(
                        Mage::helper('core')->__('Failed to delete order item: %s', $e->getMsg())
                    );
                };
            }
        }

        // Finally delete the order itself
        try {
            $order->delete();
        } catch(Exception $e) {
            Mage::getModel('adminhtml/session')->addNotice(
                Mage::helper('core')->__('Failed to delete order-item: %s', $e->getMsg())
            );
        };

        // Delete any quotes referring to this order 
        $quoteCollection = Mage::getModel('sales/quote')->getCollection()
            ->addFieldToFilter('reserved_order_id', $orderIncrementId)
        ;
        foreach($quoteCollection as $quote) {
            $quote->delete();
        }

        // Make sure the grid is fixed anyway
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid');
        $query = 'DELETE FROM `'.$table.'` WHERE `entity_id` = '.$orderId;
        try { $db->query($query); } catch(Exception $e) {}

        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_invoice_grid');
        $query = 'DELETE FROM `'.$table.'` WHERE `order_id` = '.$orderId;
        try { $db->query($query); } catch(Exception $e) {}

        // Restore the original increment IDs
        if(Mage::getStoreConfig('deleteanyorder/settings/resetincrements', $orderStoreId) == 1) {
            $this->resetOrderIncrementId($orderStoreId);
            $this->resetInvoiceIncrementId($orderStoreId);
            $this->resetShipmentIncrementId($orderStoreId);
        }

        return true;
    }

    /**
     * Delete an abstract collection by looping throw all its items
     *
     * @param collection $collection
     * @return bool
     */
    public function deleteCollection($collection)
    {
        if(!empty($collection)) {
            foreach($collection as $item) {

                // Try to delete all the comments (sales/order_***_comment)
                $comments = null;
                try {
                    $comments = $item->getCommentsCollection();
                } catch(Exception $e) {};
                if(!empty($comments)) $this->deleteCollection($comments);

                // Try to delete all the items (sales/order_***_item)
                $items = null;
                try {
                    $items = $item->getItemsCollection();
                } catch(Exception $e) {};
                if(!empty($items)) $this->deleteCollection($items);

                // Try to delete the item itself
                try {
                    if(is_object($item)) $item->delete();
                } catch(Exception $e) {};
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
     * @return array
     */
    protected function getOrderRelatedCollection($order, $name)
    {
        try {
            $collection = Mage::getResourceModel($name)
                ->addAttributeToSelect('*')
                ->setOrderFilter($order->getId());
            return $collection;
        } catch(Exception $e) {
            return array();
        };
    }

    /**
     * Optionally reset the available stock
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return bool
     */
    protected function resetStock($item)
    {
        $qty = $item->getQtyOrdered() - $item->getQtyCanceled();
        $children = $item->getChildrenItems();

        if ($item->getId() && ($productId = $item->getProductId()) && empty($children) && $qty) {
            $product = Mage::getModel('catalog/product')->load($productId);
            Mage::getSingleton('cataloginventory/stock')->backItemQty($productId, $qty);
            Mage::getModel('adminhtml/session')->addNotice(Mage::helper('core')->__('Reset stock of product "%s" with quantity %d', $product->getName(), $qty));
        }

        return true;
    }

    /**
     * Reset the increment-ID of orders
     *
     * @param int $storeId
     * @return bool
     */
    protected function resetOrderIncrementId($storeId)
    {
        $lastOrder = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('store_id', $storeId)
            ->addAttributeToSort('increment_id', 'DESC')
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem()
        ;

        if(empty($lastOrder)) {
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
     * @return bool
     */
    protected function resetInvoiceIncrementId($storeId)
    {
        $lastInvoice = Mage::getModel('sales/order_invoice')->getCollection()
            ->addFieldToFilter('store_id', $storeId)
            ->addAttributeToSort('increment_id', 'DESC')
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem()
        ;

        if(empty($lastInvoice)) {
            return false;
        }

        $newIncrementId = $lastInvoice->getIncrementId();
        $entityTypeModel = Mage::getSingleton('eav/config')->getEntityType('invoice');
        $entityStoreConfig = Mage::getModel('eav/entity_store')->loadByEntityStore($entityTypeModel->getId(), $storeId);
        $entityStoreConfig->setIncrementLastId($newIncrementId);
        try {
            $entityStoreConfig->save();
        } catch(Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Reset the increment-ID of shipments
     *
     * @param int $storeId
     * @return bool
     */
    protected function resetShipmentIncrementId($storeId)
    {
        $lastShipment = Mage::getModel('sales/order_shipment')->getCollection()
            ->addFieldToFilter('store_id', $storeId)
            ->addAttributeToSort('increment_id', 'DESC')
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem()
        ;

        if(empty($lastShipment)) {
            return false;
        }

        $newIncrementId = $lastShipment->getIncrementId();
        $entityTypeModel = Mage::getSingleton('eav/config')->getEntityType('shipment');
        $entityStoreConfig = Mage::getModel('eav/entity_store')->loadByEntityStore($entityTypeModel->getId(), $storeId);
        $entityStoreConfig->setIncrementLastId($newIncrementId);
        try {
            $entityStoreConfig->save();
        } catch(Exception $e) {
            return false;
        }

        return true;
    }
}
