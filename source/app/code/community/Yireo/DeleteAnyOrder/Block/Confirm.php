<?php
/**
 * Yireo DeleteAnyOrder for Magento 
 *
 * @package     Yireo_DeleteAnyOrder
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright (C) 2014 Yireo (http://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

/*
 * Class for block "deleteanyorder_confirm"
 */
class Yireo_DeleteAnyOrder_Block_Confirm extends Mage_Adminhtml_Block_Widget
{
    /*
     * Constructor method
     *
     * @access public
     * @param null
     * @return null
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('deleteanyorder/confirm.phtml');
        $this->init();
    }

    /*
     * Helper to set al order information
     *
     * @access public
     * @param null
     * @return string
     */
    public function init()
    {
        $order_id = $this->getRequest()->getParam('order_id', 0);
        $order_ids = $this->getRequest()->getParam('order_ids', 0);
        if($order_id > 0) {
            $order_ids = array($order_id);
        }

        $orders = array();
        foreach($order_ids as $order_id) {
            try {
                $orders[] = Mage::getModel('sales/order')->load($order_id);
            } catch(Exception $e) {
                Mage::getModel('adminhtml/session')->addError('Failed to load order: '.$e->getMessage());
            }
        }

        $this->setOrders($orders);
    }

    /*
     * Helper to return the header of this page
     *
     * @access public
     * @param string $title
     * @return string
     */
    public function getHeader($title = null)
    {
        return 'Delete any order - '.$this->__($title);
    }

    /**
     * Return the delete URL
     *
     * @access public
     * @param null
     * @return string
     */
    public function getDeleteUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/deleteanyorder/delete', array(
            '_current' => true,
            'back' => null,
            'id' => $this->getRequest()->getParam('id', 0),
        ));
    }

    /**
     * Return the back URL
     *
     * @access public
     * @param null
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('adminhtml/deleteanyorder/index');
    }

    /**
     * Render block HTML
     *
     * @access protected
     * @param null
     * @return string
     */
    protected function _toHtml()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('deleteanyorder')->__('Delete'),
                    'onclick'   => 'deleteanyorderForm.submit(\''.$this->getDeleteUrl().'\')',
                    'class' => 'delete'
                ))
        );

        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('deleteanyorder')->__('Back'),
                    'onclick'   => 'setLocation(\''.$this->getBackUrl().'\')',
                    'class' => 'back'
                ))
        );

        return parent::_toHtml();
    }
}
