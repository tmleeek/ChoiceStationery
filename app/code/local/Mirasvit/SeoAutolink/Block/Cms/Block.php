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



if (Mage::helper('mstcore')->isModuleInstalled('TM_Core') && class_exists('TM_Core_Block_Cms_Block')) {
    abstract class Mirasvit_SeoAutolink_Block_Cms_Block_Abstract extends TM_Core_Block_Cms_Block {
    }
} else {
    abstract class Mirasvit_SeoAutolink_Block_Cms_Block_Abstract extends Mage_Cms_Block_Block {
    }
}

class Mirasvit_SeoAutolink_Block_Cms_Block extends Mirasvit_SeoAutolink_Block_Cms_Block_Abstract
{
    public function getConfig()
    {
        return Mage::getSingleton('seoautolink/config');
    }

    /**
     * Prepare Content HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!in_array(Mirasvit_SeoAutolink_Model_Config_Source_Target::CMS_BLOCK, $this->getConfig()->getTarget())) {
            return parent::_toHtml();
        }
        $html = parent::_toHtml();
        $html = Mage::helper('seoautolink')->addLinks($html);

        return $html;
    }
}
