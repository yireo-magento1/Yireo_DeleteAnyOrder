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
 * DeleteAnyOrder helper
 */
class Yireo_DeleteAnyOrder_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get the entity type ID
     *
     * @param string $entity_type
     * @return int
     */
    public function getEntityTypeId($entity_type = null)
    {
        static $rows = null;
        if(empty($rows)) {
            $db = Mage::getSingleton('core/resource')->getConnection('core_read');
            $table = Mage::getSingleton('core/resource')->getTableName('eav_entity_type');
            $query = 'SELECT * FROM `'.$table.'`';
            $rs = $db->query($query);
        }

        if(!empty($rs)) {
            while($row = $rs->fetch()) {
                if($entity_type == $row['entity_type_code']) {
                    return $row['entity_type_id'];
                }
            }
        }

        return false;
    }
}
