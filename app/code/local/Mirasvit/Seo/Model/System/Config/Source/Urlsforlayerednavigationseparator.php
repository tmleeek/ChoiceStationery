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


class Mirasvit_Seo_Model_System_Config_Source_Urlsforlayerednavigationseparator
{
    public function toOptionArray()
    {
        return array(
            array('value' => Mirasvit_Seo_Model_Config::LN_NO_SEPARATOR, 'label'=>Mage::helper('seo')->__('Do not use a separator')),
            array('value' => Mirasvit_Seo_Model_Config::LN_SEPARATOR_UNDERSCORE, 'label'=>Mage::helper('seo')->__('Use "_" as a separator')),
            /*array('value' => Mirasvit_Seo_Model_Config::LN_SEPARATOR_PLUS, 'label'=>Mage::helper('seo')->__('Use "+" as a separator')),*/
        );
    }
}