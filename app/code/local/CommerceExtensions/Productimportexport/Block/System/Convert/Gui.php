<?php

class CommerceExtensions_Productimportexport_Block_System_Convert_Gui extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'system_convert_gui';
        $this->_blockGroup = 'productimportexport';
        
        $this->_headerText = Mage::helper('productimportexport')->__('Profiles');
        $this->_addButtonLabel = Mage::helper('productimportexport')->__('Add New Profile');

        parent::__construct();
    }
}