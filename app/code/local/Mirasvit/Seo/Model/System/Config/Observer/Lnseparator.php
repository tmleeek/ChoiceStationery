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


class Mirasvit_Seo_Model_System_Config_Observer_Lnseparator extends Varien_Object
{
	public function getConfig()
    {
        return Mage::getSingleton('seo/config');
    }

    /**
     * Truncates m_seofilter_rewrite table to apply newly selected LN separator
     * on "Separator between words in complex filter names" option change.
     */
	public function layeredNavigationSeparatorChange($e)
	{
        $controllreAction = $e->getEvent()->getControllerAction();
        if (!$controllreAction) {
            return;
        }

        $params = $controllreAction->getRequest()->getParams();
        if (isset($params['section']) 
            && $params['section'] == 'seo'
            && isset($params['groups']['url']['fields']['layered_navigation_friendly_urls_separator']['value'])) {
                if ($this->getConfig()->isEnabledSeoUrls() && $this->getConfig()->getFilterSeparator() != $params['groups']['url']['fields']['layered_navigation_friendly_urls_separator']['value']) {
                    Mage::getResourceModel('seofilter/rewrite')->truncate();
                    // $message = "LN Separator Saved!";
                    // Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('seo')->__($message));
                }
        }
    }
}