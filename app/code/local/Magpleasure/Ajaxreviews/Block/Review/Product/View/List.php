<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Review_Product_View_List extends Magpleasure_Ajaxreviews_Block_Reviews
{
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $display = $this->_helper()->getConfigValue('general', 'display');
        $moduleName = $this->getRequest()->getModuleName();
        $cntrlName = $this->getRequest()->getControllerName();
        $isProductPage = $this->_helper()->isInCatalogProductPage($moduleName, $cntrlName, $this->getProduct()->getId(), Mage::registry('current_product'));
        if ($isProductPage) {
            if (Magpleasure_Ajaxreviews_Model_System_Config_Source_Display::CUSTOM_PLACE == $display ||
                Magpleasure_Ajaxreviews_Model_System_Config_Source_Display::ADDITIONAL_PRODUCT_INFO == $display
            ) {
                return false;
            }
        } else {
            /** For search indexing */
            if (Magpleasure_Ajaxreviews_Model_System_Config_Source_Display::DISABLED != $display) {
                $this->setTemplate('ajaxreviews/product/view/list.phtml');
            }
        }
        if (Magpleasure_Ajaxreviews_Model_System_Config_Source_Display::STANDARD == $display) {
            $this->setTemplate('ajaxreviews/product/view/list.phtml');
        }
        return parent::_toHtml();
    }
}