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
 * Class for block "deleteanyorder_analyze"
 */
class Yireo_DeleteAnyOrder_Block_Analyze extends Mage_Adminhtml_Block_Widget
{
    /**
     * Constructor method
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('deleteanyorder/analyze.phtml');
    }

    /**
     * Return the result of the analysis
     *
     * @return array
     */
    public function getAnalysis()
    {
        return Mage::getModel('deleteanyorder/database')->getAnalysis();
    }

    /**
     * Return a listing of increment-IDs
     *
     * @return array
     */
    public function getIncrementIds()
    {
        return array(
            'current' => array(
                'order' => Mage::getModel('deleteanyorder/database')->getCurrentIncrementId('order'),
                'invoice' => Mage::getModel('deleteanyorder/database')->getCurrentIncrementId('invoice'),
                'creditmemo' => Mage::getModel('deleteanyorder/database')->getCurrentIncrementId('creditmemo'),
            ),
            'last' => array(
                'order' => Mage::getModel('deleteanyorder/database')->getLastIncrementId('order'),
                'invoice' => Mage::getModel('deleteanyorder/database')->getLastIncrementId('invoice'),
                'creditmemo' => Mage::getModel('deleteanyorder/database')->getLastIncrementId('creditmemo'),
            ),
        );
    }

    /**
     * Helper to return the header of this page
     *
     * @param string $title
     * @return string
     */
    public function getHeader($title = null)
    {
        return 'Delete any order - '.$this->__($title);
    }

    /**
     * Return the cleanup URL
     *
     * @return string
     */
    public function getCleanupUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/deleteanyorder/cleanup', array(
            '_current' => true,
            'back' => null,
        ));
    }

    /**
     * Return the back URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('adminhtml/deleteanyorder/index');
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->setChild('cleanup_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('deleteanyorder')->__('Clean-up'),
                    'onclick'   => 'deleteanyorderForm.submit(\''.$this->getCleanupUrl().'\')',
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
