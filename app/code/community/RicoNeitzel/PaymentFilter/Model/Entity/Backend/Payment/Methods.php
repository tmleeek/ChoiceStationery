<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   RicoNeitzel
 * @package    RicoNeitzel_PaymentFilter
 * @copyright  Copyright (c) 2011 Vinai Kopp http://netzarbeiter.com/
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backend model for attribute with multiple values, Netzarbeiter_ProductPayments version
 *
 * @category   Netzarbeiter
 * @package    Netzarbeiter_ProductPayments
 * @author     Vinai Kopp <vinai@netzarbeiter.com>
 */
class RicoNeitzel_PaymentFilter_Model_Entity_Backend_Payment_Methods
	extends Mage_Eav_Model_Entity_Attribute_Backend_Array
{
    public function beforeSave($object)
    {
        $data = $object->getData($this->getAttribute()->getAttributeCode());

        if (! isset($data)) $data = array();
		elseif (is_string($data)) $data = explode(',', $data);
		elseif (! is_array($data)) $data = array();

        $object->setData($this->getAttribute()->getAttributeCode(), $data);

        /**
         * Mage_Eav_Model_Entity_Attribute_Backend_Array::beforeSave() makes a string from the array values
         */
        return parent::beforeSave($object);
    }
}
