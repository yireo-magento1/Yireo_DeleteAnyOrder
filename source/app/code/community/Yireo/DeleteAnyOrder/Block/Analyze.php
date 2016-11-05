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
 * Class for block "deleteanyorder_analyze"
 */
class Yireo_DeleteAnyOrder_Block_Analyze extends Mage_Adminhtml_Block_Widget
{
    /** @var Yireo_DeleteAnyOrder_Model_Database */
    protected $databaseModel;

    /**
     * Constructor method
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('deleteanyorder/analyze.phtml');
        $this->databaseModel = Mage::getModel('deleteanyorder/database');
    }

    /**
     * Return the result of the analysis
     *
     * @return array
     */
    public function getAnalysis()
    {
        return $this->databaseModel->getAnalysis();
    }

    /**
     * Return a listing of increment-IDs
     *
     * @return array
     */
    public function getIncrementIds()
    {
        $database = $this->databaseModel;

        return array(
            'current' => array(
                'order' => $database->getCurrentIncrementId('order'),
                'invoice' => $database->getCurrentIncrementId('invoice'),
                'creditmemo' => $database->getCurrentIncrementId('creditmemo'),
            ),
            'last' => array(
                'order' => $database->getLastIncrementId('order'),
                'invoice' => $database->getLastIncrementId('invoice'),
                'creditmemo' => $database->getLastIncrementId('creditmemo'),
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
        return $this->getUrl('adminhtml/deleteanyorder/cleanup', array(
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
                    'label'     => $this->__('Clean-up'),
                    'onclick'   => 'deleteanyorderForm.submit(\''.$this->getCleanupUrl().'\')',
                    'class' => 'delete'
                ))
        );

        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => $this->__('Back'),
                    'onclick'   => 'setLocation(\''.$this->getBackUrl().'\')',
                    'class' => 'back'
                ))
        );

        return parent::_toHtml();
    }
}
