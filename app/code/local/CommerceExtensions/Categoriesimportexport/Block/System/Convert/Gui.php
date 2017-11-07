<?php

class CommerceExtensions_Categoriesimportexport_Block_System_Convert_Gui extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'system_convert_gui';
        $this->_blockGroup = 'categoriesimportexport';
        
        $this->_headerText = Mage::helper('categoriesimportexport')->__('Profiles');
        $this->_addButtonLabel = Mage::helper('categoriesimportexport')->__('Add New Profile');

        parent::__construct();
    }
}