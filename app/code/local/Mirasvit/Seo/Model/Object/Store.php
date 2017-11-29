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


class Mirasvit_Seo_Model_Object_Store extends Varien_Object
{
    public function _construct() {
        $this->setData(Mage::app()->getStore()->getData());
        if (Mage::getStoreConfig('general/store_information/name')) {
            $this->setName(Mage::getStoreConfig('general/store_information/name'));
        }
        $this->setPhone(Mage::getStoreConfig('general/store_information/phone'));
        $this->setAddress(Mage::getStoreConfig('general/store_information/address'));
        $this->setEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
        $this->setUrl(Mage::getBaseUrl());
    }

}