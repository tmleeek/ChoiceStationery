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


class Mirasvit_Seo_Model_Keywords_Observer extends Varien_Object
{
	protected $_isKeywordsChecked = false;

	public function getConfig()
    {
        return Mage::getSingleton('seo/config');
    }

	public function deleteKeywordTag($e)
    {
    	if ($this->getConfig()->isRemoveKeywordsMetaTag(Mage::app()->getStore()->getId()) 
    		&& !$this->_isKeywordsChecked 
    		&& $e->getData('block')->getNameInLayout() == "head") {

	        $transport = $e->getTransport();
	        $html = $transport->getHtml();
	        $html = preg_replace('/<meta name="keywords" content=".*>[\n\r]/i', '', $html, 1, $count);
	        if ($count) {
	        	$this->_isKeywordsChecked = true;
	        }
	        $transport->setHtml($html);
    	}
    }
}