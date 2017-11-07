<?php

class CommerceExtensions_Productimportexport_Block_System_Convert_Gui_Edit_Tab_Wizard extends Mage_Adminhtml_Block_System_Convert_Gui_Edit_Tab_Wizard
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('productimportexport/system/convert/profile/wizard.phtml');
    }
	
	public function getAttributes($entityType)
    {
        if (!isset($this->_attributes[$entityType])) {
            switch ($entityType) {
                case 'product':
                    $attributes = Mage::getSingleton('catalog/convert_parser_product')
                        ->getExternalAttributes();
                    break;
            }

            array_splice($attributes, 0, 0, array(''=>$this->__('Choose an attribute')));
            switch ($entityType) {
                case 'product':
					$attributes['associated'] = "associated";
					$attributes['grouped'] = "grouped";
					$attributes['group_price_price'] = "group_price_price";
					$attributes['super_attribute_pricing'] = "super_attribute_pricing";
					$attributes['tier_prices'] = "tier_prices";
					$attributes['categories'] = "categories";
                    break;
            }
            $this->_attributes[$entityType] = $attributes;
        }
        return $this->_attributes[$entityType];
    }
}

