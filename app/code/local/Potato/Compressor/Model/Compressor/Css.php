<?php

class Potato_Compressor_Model_Compressor_Css extends Potato_Compressor_Model_Compressor_Abstract
{
    const FILE_EXTENSION   = 'css';
    const SMALL_CSS_LENGTH = 5120;//5kb

    public function compression($content)
    {
        $cssCompressor = new Compressor_Minify_CSS;
        $content = $cssCompressor->minify($this->removeComments($content));
        $content = str_replace(" }", "}", $content);
        $content = str_replace("} ", "}", $content);
        $content = str_replace(": ", ":", $content);
        $content = str_replace(" :", ":", $content);
        $content = str_replace("  ", " ", $content);
        return $content;
    }

    public function removeComments($content)
    {
        //remove comments
        $content = preg_replace('!/\*.*?\*/!s', '', $content);
        $content = preg_replace('/\n\s*\n/', "\n", $content);
        return $content;
    }

    public function makeInline($response)
    {
        $body = $response->getBody();
        //get all css link tags
        preg_match_all('/<link.*href=.*>/', $body, $matches);
        if (empty($matches)) {
            return $this;
        }
        foreach ($matches[0] as $line) {
            //check ignore
            preg_match('@po_cmp_ignore@', $line, $match);
            if (!empty($match)) {
                continue;
            }
            //check type text/css attribute
            preg_match("/<link.*type=.*text\/css.*>/", $line, $match);
            if (empty($match)) {
                continue;
            }
            //get href attribute
            preg_match("/(<link[^>]*href*= *[\"']?)([^\"']*)/is", $line, $match);
            if (empty($match) || !isset($match[2])) {
                continue;
            }
            $linkUrl = $match[2];
            $content = $this->_getLinkContent($linkUrl);
            if (!$content) {
                continue;
            }
            //get media attribute
            preg_match("/(<link[^>]*media*= *[\"']?)([^\"']*)/is", $line, $match);
            $media = 'all';
            if (!empty($match) && isset($match[2])) {
                $media = $match[2];
            }
            $styleLine = '<style media="'.$media.'">' . $content . '</style>';
            //replace link to style code
            $body = str_replace($line, $styleLine, $body);
        }
        $response->setBody($body);
        return $this;
    }

    protected function _getLinkContent($linkUrl)
    {
        $baseUrl = trim(Mage::getBaseUrl('media', Mage::app()->getRequest()->isSecure()), '/');
        if (strpos($linkUrl, $baseUrl) !== false) {
            $filePath = str_replace($baseUrl, rtrim(Mage::getBaseDir('media'), '/'), $linkUrl);
            if (!@file_exists($filePath) || filesize($filePath) > self::SMALL_CSS_LENGTH) {
                return false;
            }
            return file_get_contents($filePath);
        }
        return false;
    }
}