<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Admin_System_Config_Form_Fieldset_FormFields extends Mage_Adminhtml_Block_System_Config_Form_Fieldset{

	protected $_dummyElement;
    protected $_fieldRenderer;
    protected $_values;
	
	public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
		
		$allOptions = Mage::getModel('sublogin/source_formfields')->getAllOptions();
		foreach ( $allOptions as $option){
			if ($option['value'] == "") {
				continue;
			}
			$html.= $this->_getFieldHtml($element, $option);
		}
		
        $html .= $this->_getFooterHtml($element);
 
        return $html;
    }
    
    //this creates a dummy element so you can say if your config fields are available on default and website level - you can skip this and add the scope for each element in _getFieldHtml method
    protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = new Varien_Object(array('show_in_default'=>1, 'show_in_website'=>1));
        }
        return $this->_dummyElement;
    }
    
    //this sets the fields renderer. If you have a custom renderer tou can change this. 
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }
        return $this->_fieldRenderer;
    }
    
    //this is usefull in case you need to create a config field with type dropdown or multiselect. For text and texareaa you can skip it.
    protected function _getValues()
    {
        if (empty($this->_values)) {
            $this->_values = array(
                array('label'=>Mage::helper('sublogin')->__('No'), 'value'=>0),
                array('label'=>Mage::helper('sublogin')->__('Yes'), 'value'=>1),
            );
        }
        return $this->_values;
    }
    
    //this actually gets the html for a field
    protected function _getFieldHtml($fieldset, $option)
    {
        $configData = $this->getConfigData();
        $path = 'sublogin/form_fields_default_values/'.$option['value'];//this value is composed by the section name, group name and field name. The field name must not be numerical (that's why I added 'group_' in front of it)
        if (isset($configData[$path])) {
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data = ""; //(int)(string)$this->getForm()->getConfigRoot()->descend($path);
            $inherit = true;
        }
 
        $e = $this->_getDummyElement();//get the dummy element
 
		$fieldType = $this->getFieldType($option['value']);		
        $field = $fieldset->addField('sublogin_form_fields_default_values_'.$option['value'], $fieldType,//this is the type of the element (can be text, textarea, select, multiselect, ...)
            array(
                'name'          => 'groups[form_fields_default_values][fields]['.$option['value'].'][value]',//this is groups[group name][fields][field name][value]
                'label'         => $option['label'],//this is the label of the element
                'value'         => $data, //this is the current value
                // 'values'        => $this->_getValues(),//this is necessary if the type is select or multiselect
                'inherit'       => $inherit,
                'can_use_default_value' => $this->getForm()->canUseDefaultValue($e), //sets if it can be changed on the default level
                'can_use_website_value' => $this->getForm()->canUseWebsiteValue($e), //sets if can be changed on website level
            ))->setRenderer($this->_getFieldRenderer());
            
       if ($fieldType == 'select')
       {
			$field->setValues(array(
				array(
					'label'	=>	Mage::helper('sublogin')->__('Yes'),
					'value'	=>	1,
				),
				array(
					'label'	=>	Mage::helper('sublogin')->__('No'),
					'value'	=>	0,
				),
			));
	   }
       
       if ($option['value'] == 'expire_date')
       {
		   $afterElementHtml = '<p class="note"><span>'.Mage::helper('sublogin')->__('Format: %s (Year-Month-Date)', date('Y-m-d')).'</span></p>';
		   $field->setData('after_element_html', $afterElementHtml);
	   }
	   
	   if ($option['value'] == 'acl')
       {
		   $afterElementHtml = '<p class="note"><span>'.Mage::helper('sublogin')->__('Separate multiple entries by comma. Example: acl_identifier1,acl_identifier2').'</span></p>';
		   $field->setData('after_element_html', $afterElementHtml);
	   }
 
       return $field->toHtml();
    }
    
	protected function getFieldType($field)
	{
		switch ($field)
		{
			case "send_backendmails":
			case "create_sublogins":
			case "is_subscribed":
			case "order_needs_approval":
			case "active":
				return 'select';
			break;
			case "acl":
				return 'textarea';
			break;
		}
		return 'text';
	}
}