<?php

class HS_Defer_Block_Adminhtml_System_Config_Form_Field_Array
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('expr', array(
            'label' => Mage::helper('adminhtml')->__('Expression'),
            'style' => 'width:120px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Match');
        parent::__construct();
    }
}