<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 BoostMyshop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Model_System_Config_Source_Attribute extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAllOptions() {
        if (!$this->_options) {
            $entityTypeId = Mage::getModel('eav/entity_type')->loadByCode('catalog_product')->getId();
            $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                    ->setEntityTypeFilter($entityTypeId)
                    ->addFieldToFilter('backend_type', array('neq' => 'static'));

            //add empty
            $options[] = array(
                'value' => '',
                'label' => '',
            );

            foreach ($attributes as $attribute) {
                $options[] = array(
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getName(),
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