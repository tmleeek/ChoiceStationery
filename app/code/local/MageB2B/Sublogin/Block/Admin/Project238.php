<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Admin_Project238 extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('adminhtml/sublogin/project238');
        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel('Run setup')
            ->setOnClick("setLocation('$url')");
        if ($this->_checkIfAlreadyInstalled())
        {
            $html->setClass('scalable disabled');
            $html->setAttribute('disabled', 'disabled');
        }
        return $html->toHtml();
    }

    /**
     * check if attribute was installed, or lets say button was clicked
     * @return bool
     */
    protected function _checkIfAlreadyInstalled()
    {
        $attribute = Mage::getModel('catalog/resource_eav_attribute')->loadByCode('customer','kcp');
        if ($attribute->getData('attribute_code'))
            return true;
        return false;
    }
}
