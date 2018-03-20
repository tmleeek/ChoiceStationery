<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */


class Magpleasure_Common_Model_Eav_Attribute extends Mage_Eav_Model_Entity_Attribute
{
    protected $_options;

    public function getOptions($storeId = null)
    {
        if (!$this->_options) {
            /** @var $options Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection */
            $options = Mage::getResourceModel('eav/entity_attribute_option_collection');
            $options
                ->setStoreFilter($storeId ? Mage::app()->getStore()->getId() : $storeId)
                ->setPositionOrder()
                ->addFieldToFilter('attribute_id', $this->getAttributeId());
            $this->_options = $options;
        }
        return $this->_options;
    }
}
