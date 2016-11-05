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
 * DeleteAnyOrder Core model
 */
class Yireo_DeleteAnyOrder_Model_Core
{
    /**
     * Get a list of all the involved order-models
     *
     * @return array
     */
    public function getOrderModels()
    {
        $models = array(
            'invoice',
            'shipment',
            'shipment_track',
            'creditmemo',
        );

        foreach ($models as $index => $model) {
            $models[$index] = 'sales/order_' . $model;
        }

        return $models;
    }
}
