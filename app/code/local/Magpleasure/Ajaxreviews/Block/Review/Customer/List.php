<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Review_Customer_List extends Mage_Review_Block_Customer_List
{
    /**
     * Before rendering HTML
     *
     * @return Mage_Core_Block_Abstract
     */
    public function _beforeToHtml()
    {
        if (Mage::helper('ajaxreviews')->replaceStandardRate()) {
            $this->setTemplate('ajaxreviews/customer/list.phtml');
        }
        return parent::_beforeToHtml();
    }
}