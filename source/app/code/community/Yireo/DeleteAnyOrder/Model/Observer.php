<?php
/**
 * Yireo DeleteAnyOrder
 *
 * @author Yireo
 * @package DeleteAnyOrder
 * @copyright Copyright 2014
 * @license Open Source License (OSL v3)
 * @link http://www.yireo.com
 */

/*
 * DeleteAnyOrder observer to various Magento events
 */
class Yireo_DeleteAnyOrder_Model_Observer extends Mage_Core_Model_Abstract
{
    /*
     * Method fired on the event <controller_action_predispatch>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_DeleteAnyOrder_Model_Observer
     */
    public function controllerActionPredispatch($observer)
    {
        // Run the feed
        Mage::getModel('deleteanyorder/feed')->updateIfAllowed();
    }
}
