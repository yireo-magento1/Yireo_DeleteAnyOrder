<?php
/**
 * Yireo DeleteAnyOrder for Magento 
 *
 * @package     Yireo_DeleteAnyOrder
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright (C) 2014 Yireo (http://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

class Yireo_DeleteAnyOrder_Block_Overview extends Mage_Adminhtml_Block_Widget_Container
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
        $this->setTemplate('deleteanyorder/overview.phtml');
        parent::_construct();
    }

    /*
     * Overriden method to add the grid to this layout
     *
     * @access public
     * @param null
     * @return null
     */
    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()
            ->createBlock('deleteanyorder/overview_grid', 'deleteanyorder.grid')
            ->setSaveParametersInSession(true)
        );
        return parent::_prepareLayout();
    }

    /*
     * Helper method to get the grid output
     *
     * @access public
     * @param null
     * @return null
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
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
        return 'Delete Any Order - '.$this->__($title);
    }

    /**
     * Return the delete URL
     *
     * @access public
     * @param null
     * @return string
     */
    public function getAnalyzeUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/deleteanyorder/analyze', array(
            '_current' => true,
            'back' => null,
        ));
    }

    /**
     * Return the delete URL
     *
     * @access public
     * @param null
     * @return string
     */
    public function getVersion()
    {
        $config = Mage::app()->getConfig()->getModuleConfig('Yireo_DeleteAnyOrder');
        return (string)$config->version;
    }
}
