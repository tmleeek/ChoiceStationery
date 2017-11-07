<?php

class MDN_Quotation_Model_System_Config_Source_Attributeset extends Mage_Core_Model_Abstract {

    private $_options = null;

    public function getAllOptions() {
        if (!$this->_options) {
            $options = array();
            $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
                            ->setEntityTypeFilter(Mage::getSingleton('Quotation/Constant')->getProductEntityId());
            foreach ($collection as $item) {
                $options[] = array(
                    'value' => $item->getId(),
                    'label' => $item->getattribute_set_name()
                );
            }
            $this->_options = $options;
        }
        return $this->_options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }

}