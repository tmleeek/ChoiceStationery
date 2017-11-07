<?php

class MDN_Quotation_Model_System_Config_Source_QuoteRequestMode extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAllOptions() {
        if (!$this->_options) {

            $options[] = array('value' => 'no', 'label' => Mage::helper('quotation')->__('No'));
            $options[] = array('value' => 'only_enabled', 'label' => Mage::helper('quotation')->__('Only enabled products'));
            $options[] = array('value' => 'all', 'label' => Mage::helper('quotation')->__('All products'));

            $this->_options = $options;
        }
        return $this->_options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }

}