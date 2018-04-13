<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
 */
class Amasty_Meta_Model_System_Store extends Mage_Adminhtml_Model_System_Store
{
	/**
	 * Retrieve store values for form
	 *
	 * @param bool $empty
	 * @param bool $all
	 * @return array
	 */
	public function getStoreValuesForForm($empty = false, $all = false)
	{
		$options = parent::getStoreValuesForForm($empty, $all);

		if ($empty) {
			$options[0] = array(
				'label' => Mage::helper('ammeta')->__('Default'),
				'value' => 0
			);
		}

		return $options;
	}
}
