<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Review_Customer_View extends Mage_Review_Block_Customer_View
{
    /**
     * Before rendering HTML
     *
     * @return Mage_Core_Block_Abstract
     */
    public function _beforeToHtml()
    {
        if (Mage::helper('ajaxreviews')->replaceStandardRate()) {
            $this->setTemplate('ajaxreviews/customer/view.phtml');
        }
        return parent::_beforeToHtml();
    }
}