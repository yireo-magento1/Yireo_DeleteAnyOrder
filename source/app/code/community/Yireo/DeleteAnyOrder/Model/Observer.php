<?php
/**
 * Yireo DeleteAnyOrder
 *
 * @author Yireo
 * @package DeleteAnyOrder
 * @copyright Copyright 2015
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

    /*
     * Method fired on the event <core_block_abstract_prepare_layout_before>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_DeleteAnyOrder_Model_Observer
     */
    public function coreBlockAbstractPrepareLayoutBefore($observer)
    {
        $block = $observer->getEvent()->getBlock();

        $blockClass = 'Mage_Adminhtml_Block_Widget_Grid_Massaction';
        if($block instanceof $blockClass
            && $block->getRequest()->getControllerName() == 'sales_order')
        {
            $block->addItem('deleteanyorder', array(
                'label' => 'Delete permanently',
                'url' => Mage::helper('adminhtml')->getUrl('adminhtml/deleteanyorder/confirm'),
            ));
        }
    }
}
