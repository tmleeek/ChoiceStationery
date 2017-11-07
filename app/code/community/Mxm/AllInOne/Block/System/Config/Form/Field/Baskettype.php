<?php

class Mxm_AllInOne_Block_System_Config_Form_Field_Baskettype extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $helper = Mage::helper('mxmallinone');
        $options = array();

        $website = null;
        if ($element->getScope() === 'websites') {
            $website = $element->getScopeId();
        } else if ($element->getScope() === 'stores') {
            $website = Mage::app()->getStore($element->getScopeId())->getWebsite();
        }

        if ($helper->canUseApi($website)) {
            try {
                $basketTypes = $helper->getApi($website)->basket_type->fetchAll();
                foreach ($basketTypes as $type) {
                    $options[] = array(
                        'value' => $type['basket_type_id'],
                        'label' => $type['basket_name'],
                    );
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        $element->setValues($options);

        return $html = parent::_getElementHtml($element);
    }
}