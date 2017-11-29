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


if (Mage::helper('mstcore')->isModuleInstalled('Itactica_ExtendedReviews') && class_exists('Itactica_ExtendedReviews_Block_Helper')) {
    abstract class Mirasvit_Seo_Block_Review_Helper_Abstract extends Itactica_ExtendedReviews_Block_Helper {
    }
} elseif (Mage::helper('mstcore')->isModuleInstalled('Magpleasure_Ajaxreviews') && class_exists('Magpleasure_Ajaxreviews_Block_Review_Helper')) {
    abstract class Mirasvit_Seo_Block_Review_Helper_Abstract extends Magpleasure_Ajaxreviews_Block_Review_Helper {
    }
} else {
    abstract class Mirasvit_Seo_Block_Review_Helper_Abstract extends Mage_Review_Block_Helper {
    }
}

class Mirasvit_Seo_Block_Review_Helper extends Mirasvit_Seo_Block_Review_Helper_Abstract
{
    public function getConfig()
    {
    	return Mage::getSingleton('seo/config');
    }

    public function getReviewsUrl()
    {
        if ($this->getConfig()->isEnabledReviewSeoUrls()) {
            $uri = $this->getProduct()->getUrlKey();
            if (!$uri) {
                $product = Mage::getModel('catalog/product')->load($this->getProduct()->getId());
                $uri = $product->getData('url_key');
            }
            return Mage::getUrl($uri.'/reviews');
        } else {
            return parent::getReviewsUrl();
        }
    }
}