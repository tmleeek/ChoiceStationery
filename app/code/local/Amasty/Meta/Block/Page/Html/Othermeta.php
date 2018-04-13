<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
 */

class Amasty_Meta_Block_Page_Html_Othermeta extends Mage_Core_Block_Template
{
	/**
	 * Initialize template
	 *
	 */
	protected function _construct()
	{
		$this->setTemplate('amasty/ammeta/html/othermeta.phtml');
	}

	/**
	 * @return Mage_Core_Block_Abstract
	 */
	protected function _beforeToHtml()
	{
		$hlp             = Mage::helper('ammeta');
		$configFromUrl   = $hlp->getMetaConfigByUrl();
		$otherAttributes = array('custom_canonical_url');

		if (empty($configFromUrl['custom_canonical_url']) && Mage::registry('product')) {
			$configFromUrl['custom_canonical_url'] = Mage::helper('amseotoolkit')
				->getProductUrl(Mage::registry('product'));
		}

		foreach ($otherAttributes as $attr) {
			if (! empty($configFromUrl[$attr])) {
				$this->setData($attr, $configFromUrl[$attr]);
			}
		}

		return parent::_beforeToHtml();
	}

}
