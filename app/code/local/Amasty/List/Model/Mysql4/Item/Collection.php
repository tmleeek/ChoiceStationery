<?php
/**
* @copyright Amasty.
*/ 
class Amasty_List_Model_Mysql4_Item_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amlist/item');
    } 
    
    public function joinProductName()
    {
        $entityTypeId = Mage::getResourceModel('catalog/config')
                ->getEntityTypeId();
        $attribute = Mage::getModel('catalog/entity_attribute')
            ->loadByCode($entityTypeId, 'name');

        $this->getSelect()
            ->join(
                array('product_name_table' => $attribute->getBackendTable()),
                'product_name_table.entity_id=main_table.product_id' .
                    ' AND product_name_table.store_id=0' .
                    ' AND product_name_table.attribute_id=' . $attribute->getId().
                    ' AND product_name_table.entity_type_id=' . $entityTypeId,
                array('value'=>'product_name_table.value')
            );

        return $this;
    }
    
    public function joinList()
    {
        $this->getSelect()
            ->join(
                array('amlist' => $this->getTable('amlist/list')),
                'amlist.list_id = main_table.list_id',
                array('title'=>'amlist.title')
            );

        return $this;
    }    
}
