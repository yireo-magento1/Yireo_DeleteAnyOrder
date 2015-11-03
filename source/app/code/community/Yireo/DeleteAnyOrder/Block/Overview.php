<?php
/**
 * Yireo DeleteAnyOrder for Magento 
 *
 * @package     Yireo_DeleteAnyOrder
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (http://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

class Yireo_DeleteAnyOrder_Block_Overview extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Constructor method
     *
     */
    public function _construct()
    {
        $this->setTemplate('deleteanyorder/overview.phtml');
        parent::_construct();
    }

    /**
     * Overriden method to add the grid to this layout
     *
     */
    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()
            ->createBlock('deleteanyorder/overview_grid', 'deleteanyorder.grid')
            ->setSaveParametersInSession(true)
        );
        return parent::_prepareLayout();
    }

    /**
     * Helper method to get the grid output
     *
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    /**
     * Helper to return the header of this page
     *
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
     * @return string
     */
    public function getVersion()
    {
        $config = Mage::app()->getConfig()->getModuleConfig('Yireo_DeleteAnyOrder');
        return (string)$config->version;
    }
}
