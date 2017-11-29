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


class Mirasvit_Seo_Model_Opengraph extends Mage_Core_Model_Abstract
{
    public function getConfig()
    {
        return Mage::getSingleton('seo/config');
    }

    public function modifyHtmlResponse($e)
    {
        if (strpos(Mage::helper('core/url')->getCurrentUrl(), '/checkout/onepage/')
            || strpos(Mage::helper('core/url')->getCurrentUrl(), 'onestepcheckout')) {
            return;
        }

        if (Mage::getSingleton('admin/session')->getUser()) {
            return;
        }

        if (!is_object($e) && !is_object($e->getFront())) {
            return;
        }

        $response =  $e->getFront()->getResponse();
        $config   = $this->getConfig();
        $str      = '';

        // custom fix for Quickshop quick view pages no to get indexted as duplicate content
        if (Mage::helper('seo')->getFullActionCode() == 'quickshop_index_view') {
            $body = $response->getBody();
            $body = '<html><head><meta name="robots" content="NOINDEX,NOFOLLOW"></head><body>'.$body.'</body></html>';
            $response->setBody($body);
            return;
        }
        // end of custom fix

        if ($config->isOpenGraphEnabled() && Mage::helper('seo')->getFullActionCode() == 'catalog_product_view') {
            $str .=' prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# product: http://ogp.me/ns/product#" ';
        }

        if ($str == '') {
            return;
        }

        if (!$config->isOpenGraphEnabled()) {
            return;
        }

        $body = $response->getBody();
        if (!$this->hasDoctype(trim($body))) {
            return;
        }

        $label = "<!-- mirasvit block -->";
        if (strpos($body, $label) !== false) {
            return;
        }

        $body = str_replace('<html', '<html'.$str, $body);
        if ($product = Mage::registry('current_product')) {
            //$product = Mage::getModel('catalog/product')->load($product->getId());
            $tags   = array();
            if ($config->isOpenGraphEnabled()) {
                $tags[] = $label;
                $tags[] = "<!-- mirasvit open graph begin -->";
                $tags[] = $this->createMetaTag('title', $product->getName());

                preg_match('/meta name\\=\\"description\\" content\\=\\"(.*?)\\" \\/\\>/', $body, $matches);
                if (isset($matches[1])) {
                    $tags[] = $this->createMetaTag('description', $matches[1]);
                } else {
                    $tags[] = $this->createMetaTag('description', $product->getShortDescription());
                }
                $tags[] = $this->createMetaTag('type', 'og:product');

                $canonicalUrl = str_replace('?___SID=U', '', Mage::helper('seo/canonical')->getCanonicalUrl());
                $tags[] = $this->createMetaTag('url', $canonicalUrl);

                if ($product->getImage()!='no_selection') {
                    $tags[] = $this->createMetaTag('image', Mage::helper('catalog/image')->init($product, 'image'));
                }

                foreach($product->getMediaGalleryImages() as $image) {
                    if ($image->getFile()) {
                        $tags[] = $this->createMetaTag('image', Mage::helper('catalog/image')->init($product, 'image', $image->getFile()));
                    }
                }

                if ($productFinalPrice = Mage::helper('seo')->getCurrentProductFinalPrice($product, true)) {
                    $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
                    $tags[]       = $this->createMetaTag('product:price:amount', $productFinalPrice);
                    $tags[]       = $this->createMetaTag('product:price:currency', $currencyCode);
                }

                $tags[] = "<!-- mirasvit open graph end -->";
                $tags = array_unique($tags);
            }

            $body   = str_replace('<head>', "<head>\n".implode($tags, "\n"), $body);
        }

        $response->setBody($body);
    }

    protected function createMetaTag($property, $value)
    {
        $value = Mage::helper('seo')->cleanMetaTag($value);
        if ($property == 'image') {
            $value = preg_replace('/https:\\/\\/(.*?)\\//ims', str_replace('http://', 'https://', Mage::getBaseUrl()), $value, 1);
            $value = preg_replace('/http:\\/\\/(.*?)\\//ims', str_replace('https://', 'http://', Mage::getBaseUrl()), $value, 1);
        }

        if ($property == 'product:price:amount' || $property == 'product:price:currency') {
            return "<meta property=\"$property\" content=\"$value\"/>";
        } else {
            return "<meta property=\"og:$property\" content=\"$value\"/>";
        }
    }

    protected function hasDoctype($body) {
        $doctypeCode = array('<!doctype html', '<html', '<?xml');
        foreach($doctypeCode as $doctype) {
                if (stripos($body, $doctype) === 0) {
                    return true;
                }
        }
        return false;
    }
}
