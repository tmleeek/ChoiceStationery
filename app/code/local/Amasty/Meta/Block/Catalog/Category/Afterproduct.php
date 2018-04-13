<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
 */
class Amasty_Meta_Block_Catalog_Category_Afterproduct extends Mage_Core_Block_Template
{
	/**
	 * Initialize template
	 *
	 */
	protected function _construct()
	{
		$this->setTemplate('amasty/ammeta/catalog/category/afterproduct.phtml');
	}

	protected function _toHtml()
	{
		$category = Mage::registry('current_category');

        if (!$category)
            return '';

		if ($category->getData('display_mode') == Mage_Catalog_Model_Category::DM_PAGE || ! $category->getData('after_product_text')) {
			return '';
		}
        
        if (!Mage::registry('ammeta_after_product_text')) {
            $this->setData('text', $category->getData('after_product_text'));
        } else {
            return '';
        }

		/**
		 * This fix needed to compatibility with amshopby (ajax reloading)
		 * This block does not need to output at Ajax request (because this static block)
		 */
		$isAJAX = Mage::app()->getRequest()->getParam('is_ajax', false);
		$isAJAX = $isAJAX && Mage::app()->getRequest()->isXmlHttpRequest();
		if ($isAJAX) {
			return '';
		}
        
        Mage::register('ammeta_after_product_text', true, true);
        
		return parent::_toHtml();
	}

}
