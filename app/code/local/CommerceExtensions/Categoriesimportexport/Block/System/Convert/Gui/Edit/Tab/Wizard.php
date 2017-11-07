<?php

class CommerceExtensions_Categoriesimportexport_Block_System_Convert_Gui_Edit_Tab_Wizard extends Mage_Adminhtml_Block_System_Convert_Gui_Edit_Tab_Wizard
{
    protected $_attributes;
	
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('categoriesimportexport/system/convert/profile/wizard.phtml');
    }
	
    public function getAttributes($entityType)
    {
        if (!isset($this->_attributes[$entityType])) {
            switch ($entityType) {
                case 'product':
                    $attributes = Mage::getSingleton('catalog/convert_parser_product')
                        ->getExternalAttributes();
                    break;

                case 'customer':
                    $attributes = Mage::getSingleton('customer/convert_parser_customer')
						->getExternalAttributes();
                    break;
					
                case 'category':
                    #$attributes = Mage::getSingleton('customer/convert_parser_customer')->getExternalAttributes();
					$attributesData = Mage::getModel('catalog/category')->getAttributes();
					foreach ($attributesData as $attribute_value){
						$attributes[$attribute_value->getData('attribute_code')] = $attribute_value->getData('attribute_code');
					}
                    break;
            }

            array_splice($attributes, 0, 0, array(''=>$this->__('Choose an attribute')));
            $this->_attributes[$entityType] = $attributes;
        }
        return $this->_attributes[$entityType];
    }
}

