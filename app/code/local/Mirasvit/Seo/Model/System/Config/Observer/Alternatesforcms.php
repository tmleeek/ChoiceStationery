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


class Mirasvit_Seo_Model_System_Config_Observer_Alternatesforcms extends Varien_Object
{
	public function getConfig()
    {
        return Mage::getSingleton('seo/config');
    }

    /**
     * Adds Notice Message when "Link Rel="alternate" and hreflang" option is being enabled
     */
	public function alternatesEnabled($e) {
        $controllreAction = $e->getEvent()->getControllerAction();
        if (!$controllreAction) {
            return;
        }

        $params = $controllreAction->getRequest()->getParams();
        $storeCode = $controllreAction->getRequest()->getParam('store');
        $websiteCode = $controllreAction->getRequest()->getParam('website');
        $storeId = 0;

        if ($storeCode && $websiteCode) {
            $storeId = Mage::getModel('core/store')->load($storeCode)->getId();
        } elseif (!$storeCode && $websiteCode) {
            $websiteId = Mage::getModel('core/website')->load($websiteCode)->getId();
            $storeId = Mage::app()->getWebsite($websiteId)->getDefaultStore()->getId();
        }

        if (isset($params['section']) 
            && $params['section'] == 'seo'
            && isset($params['groups']['general']['fields']['is_alternate_hreflang']['value'])) {
                if ($params['groups']['general']['fields']['is_alternate_hreflang']['value'] == 1
                    && $this->getConfig()->isAlternateHreflangEnabled($storeId) != $params['groups']['general']['fields']['is_alternate_hreflang']['value']) {
                    $message = 'To apply "Link Rel="alternate" and hreflang" to your CMS pages(like Home Page or Contacts) '
                    .'please define the same "alternate group" attribute for pages that need to be alternates of each other in '
                    . '<a target="_blank" href="' .Mage::helper('adminhtml')->getUrl('*/cms_page/index'). '"> CMS > Pages</a>';
                    Mage::getSingleton('adminhtml/session')->addNotice($message);
                }
        }        
    }
}