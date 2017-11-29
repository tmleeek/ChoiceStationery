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


class Mirasvit_Seo_Model_Snippets_Twitter extends Mage_Core_Model_Abstract
{
	protected $_isCardAdded = false;
	protected $_cardType    = false;

	public function getConfig()
    {
        return Mage::getSingleton('seo/config');
    }

    public function addCard($e)
    {
        if (!$this->_isCardAdded 
        	&& Mage::helper('seo')->getFullActionCode() == 'catalog_product_view'
        	&& $e->getData('block')->getNameInLayout() == "head")
        {

        	switch ($this->getConfig()->getIsTwitterCard(Mage::app()->getStore()->getId()))
        	{
        		case Mirasvit_Seo_Model_Config::TWITTERCARD_SMALL_IMAGE:
        			$this->_cardType = "summary";
        			break;
        		case Mirasvit_Seo_Model_Config::TWITTERCARD_LARGE_IMAGE:
        			$this->_cardType = "summary_large_image";
        			break;
        		default:
        			$this->_cardType = false;
        			break;
        	}

	        if (!$this->_cardType) {
        		return;
        	}

        	$transport = $e->getTransport();
	        $html = $transport->getHtml();

	        $tags = $this->createMetaTag('card', $this->_cardType);

	        if ($user = $this->getConfig()->getTwitterUsername()) {
	        	if (strpos($user, '@') !== 0) {
	        		$user = '@'.$user;
	        	}
	        	$tags .= $this->createMetaTag('site', $user);
	        }

        	$this->_product = Mage::registry('current_product');
	        if (!$this->_product) {
	            $this->_product = Mage::registry('product');
	        }

	        if ($this->_product) {
	        	$metaTitle = Mage::helper('seo')->getCurrentSeo()->getMetaTitle();
	        	$tags .= $this->createMetaTag('name', $metaTitle);
	        	
	        	preg_match('/meta name\\=\\"description\\" content\\=\\"(.*?)\\" \\/\\>/', $html, $matches);
                if (isset($matches[1])) {
                	$metaDescription = $matches[1];
                } else {
                	$metaDescription = str_replace('"', '', Mage::helper('seo')->getCurrentSeo()->getMetaDescription());
                }
                $tags .= $this->createMetaTag('description', $metaDescription);

	        	if ($this->_product->getImage() != 'no_selection') {
		        	if ($image = Mage::helper('catalog/image')->init($this->_product, 'image')) {
		        		$tags .= $this->createMetaTag('image', $image);
                        $tags .= $this->createMetaTag('image:alt', Mage::helper('seo/rewrite_image')->getTitle($this->_product));
		       		}
                }
	        }

	        $tags = "\n".'<!-- mirasvit twitter card begin -->'."\n" . $tags . '<!-- mirasvit twitter card end -->';
	        $html = str_replace('</title>', '</title>'.$tags, $html);
	        $this->_isCardAdded = true;
	        $transport->setHtml($html);
    	}

    }

    protected function createMetaTag($property, $value)
    {
        $value = Mage::helper('seo')->cleanMetaTag($value);
        if ($property == 'image') {
            $value = preg_replace('/https:\\/\\/(.*?)\\//ims', str_replace('http://', 'https://', Mage::getBaseUrl()), $value, 1);
            $value = preg_replace('/http:\\/\\/(.*?)\\//ims', str_replace('https://', 'http://', Mage::getBaseUrl()), $value, 1);
        }

        return "<meta name=\"twitter:$property\" content=\"$value\"/>"."\n";
    }
}