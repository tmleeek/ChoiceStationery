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
 * @package   mirasvit/extension_seositemap
 * @version   1.0.10
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


class Mirasvit_SeoSitemap_Model_Observer
{

    public function getConfig()
    {
        return Mage::getSingleton('seositemap/config');
    }

    public function registerUrlRewrite()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return;
        }
        //echo $this->getConfig()->getFrontendSitemapBaseUrl();die;
        Mage::helper('mstcore/urlrewrite')->rewriteMode('SEOSITEMAP', true);
        Mage::helper('mstcore/urlrewrite')->registerBasePath('SEOSITEMAP', $this->getConfig()->getFrontendSitemapBaseUrl());
        Mage::helper('mstcore/urlrewrite')->registerPath('SEOSITEMAP', 'MAP', '', 'seositemap_index_index');
    }

    public function checkCronStatus()
    {
        if (Mage::helper('seositemap')->checkCronStatusFunctionVersion()
            && $request = Mage::app()->getRequest()
            && Mage::app()->getRequest()->getParam('section') == 'seositemap') {
                $cronStatus = Mage::helper('mstcore/cron')->checkCronStatus(false, false, 'Cron job is required for automatic sitemap generation. Automatic generation can be configured in System > Configuration > Catalog > Google Sitemap > Generation Settings.<br/>');
                if ($cronStatus !== true) {
                    Mage::getSingleton('adminhtml/session')->addError($cronStatus);
                }
        }
    }
}