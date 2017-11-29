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


class Mirasvit_Seo_Model_System_Config_Source_Organizationsnippets_General
{
    public function toOptionArray()
    {
        return array(
        	array('value' => 0, 'label'=>Mage::helper('seo')->__('Disabled')),
            array('value' => Mirasvit_Seo_Model_Config::SNIPPETS_ORGANIZATION_JSON, 'label'=>Mage::helper('seo')->__('Organization snippets - json version')),
            array('value' => Mirasvit_Seo_Model_Config::SNIPPETS_ORGANIZATION_MICRODATA, 'label'=>Mage::helper('seo')->__('Organization snippets - microdata version')),
        );
    }
}
