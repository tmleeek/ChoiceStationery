<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_seo
 * @version   1.3.18
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


class Mirasvit_Seo_Model_Object_Abstract extends Varien_Object
{
    protected $config, $_additional, $_store;

    public function _construct()
    {
        parent::_construct();
        $this->_config = Mage::getModel('seo/config');
        $this->_additional = array(
            'category' => array(),
            'product' => array(),
        );
    }

    protected function parse($str)
    {
        $storeId = false;
        if ($this->_store) {
            $storeId = $this->_store->getId();
        }

        $result = Mage::helper('seo/parse')->parse($str, $this->_parseObjects, $this->_additional, $storeId);

        return $result;
    }


    protected function setAdditionalVariable($objectName, $variableName, $value)
    {
        $this->_additional[$objectName][$variableName] = $value;
        if (isset($this->_parseObjects['product'])) {
            if ($objectName.'_'.$variableName == 'product_final_price_minimal') {
                 $this->_parseObjects['product']->setData('final_price_minimal', $value);
            }
            if ($objectName.'_'.$variableName == 'product_final_price_range') {
                 $this->_parseObjects['product']->setData('final_price_range', $value);
            }
        }
    }

    public function addAdditionalVariables($variables)
    {
        $this->_additional = array_merge_recursive($this->_additional, $variables);
    }

    public function getAdditionalVariables() {
        return $this->_additional;
    }
}
