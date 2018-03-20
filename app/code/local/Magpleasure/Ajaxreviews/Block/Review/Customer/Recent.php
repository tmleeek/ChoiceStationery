<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Review_Customer_Recent extends Mage_Review_Block_Customer_Recent
{
    /**
     * Before rendering HTML
     *
     * @return Mage_Core_Block_Abstract
     */
    public function _beforeToHtml()
    {
        if (Mage::helper('ajaxreviews')->replaceStandardRate()) {
            $this->setTemplate('ajaxreviews/customer/recent.phtml');
        }
        return parent::_beforeToHtml();
    }
}