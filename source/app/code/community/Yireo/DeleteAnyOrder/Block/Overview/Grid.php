<?php
/**
 * Yireo DeleteAnyOrder for Magento 
 *
 * @package     Yireo_DeleteAnyOrder
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (http://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

class Yireo_DeleteAnyOrder_Block_Overview_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    /*
     * Constructor method
     *
     * @access public
     * @param null
     * @return null
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('deleteanyorder_grid');
        $this->setUseAjax(false);
    }

    /*
     * Overriden method to add the right buttons to the layout
     *
     * @access protected
     * @param null
     * @return mixed
     */
    protected function _prepareLayout()
    {
        $this->setChild('analyze_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Analyse Database'),
                    'onclick'   => 'doAnalyze()',
                    'class'   => 'delete'
                ))
        );

        return parent::_prepareLayout();
    }

    /*
     * Overriden method to prepare the grid columns
     *
     * @access protected
     * @param null
     * @return null
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        if (Mage::getSingleton('admin/session')->isAllowed('system/tools/deleteanyorder')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('deleteanyorder')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('deleteanyorder')->__('Delete'),
                            'url'     => array('base'=>'*/*/confirm'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
        }

        // Call a helper to remove all the unwanted RSS links
        $this->cleanRssLists();
    }

    /*
     * Overriden method to set the mass action select-box
     *
     * @access public
     * @param null
     * @return null
     */
    public function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('delete_order', array(
             'label'=> Mage::helper('deleteanyorder')->__('Delete'),
             'url'  => $this->getUrl('*/*/confirm'),
        ));
    }

    /*
     * Method to return a delete-URL per item
     *
     * @access public
     * @param object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('system/tools/deleteanyorder/delete')) {
            return $this->getUrl('*/*/confirm', array('order_id' => $row->getId()));
        }
        return false;
    }

    /*
     * Method to return the analyze button
     *
     * @access public
     * @param null
     * @return string
     */
    public function getAnalyzeButtonHtml()
    {
        return $this->getChildHtml('analyze_button');
    }

    /*
     * Method to return the button section
     *
     * @access public
     * @param null
     * @return null
     */
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html .= $this->getAnalyzeButtonHtml();
        return $html;
    }

    /*
     * Method to clean the internal RSS lists
     *
     * @access public
     * @param null
     * @return null
     */
    public function cleanRssLists()
    {
        $this->_rssLists = array();
    }
}
