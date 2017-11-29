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


class Mirasvit_Seo_Helper_Url extends Mage_Core_Helper_Abstract
{
    /**
     * Get base url
     *
     * @return string
     */
    public function getCurrentBaseUrl() {
        if (Mage::app()->getStore()->isCurrentlySecure()) {
            $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, true);
        } else {
            $baseUrl = Mage::getBaseUrl();
        }

        return $baseUrl;
    }

    /**
     * Check if current category is Root category
     *
     * @return bool
     */
    public function isRootCategory() {
        if (Mage::registry('current_category')
            && Mage::registry('current_category')->getId() == Mage::app()->getStore()->getRootCategoryId()) {
            return true;
        }

        return false;
    }
}
