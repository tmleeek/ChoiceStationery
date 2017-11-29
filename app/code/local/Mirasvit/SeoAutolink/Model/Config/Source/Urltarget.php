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
 * @package   mirasvit/extension_seoautolink
 * @version   1.0.14
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_SeoAutolink_Model_Config_Source_Urltarget
{
    public function toOptionArray()
    {
        return array(
            array('value' => '_self', 'label' => Mage::helper('seoautolink')->__('_self (in current window)')),
            array('value' => '_blank', 'label' => Mage::helper('seoautolink')->__('_blank (in new window)')),
            array('value' => '_parent', 'label' => Mage::helper('seoautolink')->__('_self (in own frameset)')),
            array('value' => '_top', 'label' => Mage::helper('seoautolink')->__('_top (in full current browser window)')),
        );
    }
}
