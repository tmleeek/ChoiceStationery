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


if (Mage::helper('mstcore')->isModuleInstalled('Magpleasure_Ajaxreviews') && class_exists('Magpleasure_Ajaxreviews_Block_Review_View')) {
    abstract class Mirasvit_Seo_Block_Review_View_Abstract extends Magpleasure_Ajaxreviews_Block_Review_View {
    }
} else {
    abstract class Mirasvit_Seo_Block_Review_View_Abstract extends Mage_Review_Block_View {
    }
}

class Mirasvit_Seo_Block_Review_View extends Mirasvit_Seo_Block_Review_View_Abstract
{
    public function getConfig()
    {
    	return Mage::getSingleton('seo/config');
    }

    public function getBackUrl()
    {
        if ($this->getConfig()->isEnabledReviewSeoUrls()) {
            $uri = $this->getProductData()->getUrlKey();

            return Mage::getUrl($uri.'/reviews');
        } else {
            return parent::getReviewsUrl();
        }
    }
}