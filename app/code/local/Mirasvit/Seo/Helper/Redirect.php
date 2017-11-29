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


class Mirasvit_Seo_Helper_Redirect extends Mage_Core_Helper_Abstract
{
	/**
     * @param string $urlFrom
     * @param string $urlTo
     * @return bool
     */
    public function _checkRedirectPattern($urlFrom, $urlTo, $redirectOnlyErrorPage = false) {
        if ($urlFrom == '/*' && $urlTo == '/' && $redirectOnlyErrorPage) {
            return false;
        }
        $urlFrom = preg_quote($urlFrom, '/');
        $urlFrom = str_replace('\*', '(.*?)', $urlFrom);
        $pattern = '/' . $urlFrom . '$/ims';

        if (preg_match($pattern, $urlTo)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $urlTo
     * @return bool
     */
    public function checkForLoop($urlTo) {
        $redirectArray = $this->_getRedirectedUrls();
        if (in_array($urlTo, $redirectArray)) {
            Mage::getSingleton('seo/session')->unsetData('redirects_array');
            return true;
        }

        return false;
    }

    /**
     * @param string $currentUrl
     */
    public function setFlag($currentUrl) {
        $redirectsArray = $this->_getRedirectedUrls();
        array_push($redirectsArray, $currentUrl);
        Mage::getSingleton('seo/session')->setData('redirects_array', $redirectsArray);
    }

    public function unsetFlag() {
        Mage::getSingleton('seo/session')->unsetData('redirects_array');
    }

    /**
     * @return array
     */
    public function _getRedirectedUrls() {
        $redirectArray = Mage::getSingleton('seo/session')->getData('redirects_array');
        return $redirectArray ? $redirectArray : array() ;
    }
}