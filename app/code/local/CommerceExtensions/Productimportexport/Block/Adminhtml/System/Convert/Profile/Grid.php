<?php

class CommerceExtensions_Productimportexport_Block_Adminhtml_System_Convert_Profile_Grid extends CommerceExtensions_Productimportexport_Block_Adminhtml_System_Convert_Profile_Grid_Amasty_Pure
{
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('dataflow/profile_collection')
            ->addFieldToFilter('entity_type', array('null'=>''))
            ->addFieldToFilter('is_commerce_extensions', 0);

        $this->setCollection($collection);
    }
}

