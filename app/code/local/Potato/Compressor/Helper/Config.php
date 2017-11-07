<?php

class Potato_Compressor_Helper_Config extends Mage_Core_Helper_Abstract
{
    const GENERAL_ENABLED          = 'po_compressor/general/enabled';
    const GENERAL_MINIFY_HTML      = 'po_compressor/general/minify_html';

    const JS_SETTINGS_MERGE        = 'po_compressor/js_settings/merge';
    const JS_SETTINGS_COMPRESSION  = 'po_compressor/js_settings/compression';
    const JS_SETTINGS_GZIP         = 'po_compressor/js_settings/gzip';
    const JS_SETTINGS_DEFER        = 'po_compressor/js_settings/defer';

    const CSS_SETTINGS_MERGE       = 'po_compressor/css_settings/merge';
    const CSS_SETTINGS_COMPRESSION = 'po_compressor/css_settings/compression';
    const CSS_SETTINGS_GZIP        = 'po_compressor/css_settings/gzip';
    const CSS_INLINE               = 'po_compressor/css_settings/inline';

    const ADVANCED_IMAGE_RESIZE       = 'po_compressor/advanced/image_resize';
    const ADVANCED_IMAGE_CRON_ENABLED = 'po_compressor/advanced/image_cron_enabled';
    const ADVANCED_IMAGE_BACKUP       = 'po_compressor/advanced/image_backup';
    const ADVANCED_SKIP_IMAGES        = 'po_compressor/advanced/skip_images';

    /**
     * @param null $storeId
     *
     * @return bool
     */
    static function isImageCronEnabled($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::ADVANCED_IMAGE_CRON_ENABLED, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    static function getIsCanMinifyHtml($storeId = null)
    {
        //return (bool)Mage::getStoreConfig(self::GENERAL_MINIFY_HTML, $storeId);
        return false;
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    static function getCanResizeImage($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::ADVANCED_IMAGE_RESIZE, $storeId);
    }

    static function getSkippedImages($storeId = null)
    {
        $data = trim(Mage::getStoreConfig(self::ADVANCED_SKIP_IMAGES, $storeId));
        $images = array();
        if ($data) {
            $lines = preg_split( '/\r\n|\r|\n/', $data);
            foreach ($lines as $line) {
                $line = trim(trim($line, '\\'), '/');
                if (DS == '/') {
                    $line = str_replace('\\', DS, $line);
                } else {
                    $line = str_replace('/', DS, $line);
                }
                $images[] = $line;
            }
        }
        return $images;
    }

    static function isAllowImageBackup($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::ADVANCED_IMAGE_BACKUP, $storeId);
    }

    static function isEnabled($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::GENERAL_ENABLED, $storeId);
    }

    static function canJsMerge($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::JS_SETTINGS_MERGE, $storeId);
    }

    static function canJsCompression($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::JS_SETTINGS_COMPRESSION, $storeId);
    }

    static function canJsGzip($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::JS_SETTINGS_GZIP, $storeId);
    }

    static function getDeferMethod($storeId = null)
    {
        return (int)Mage::getStoreConfig(self::JS_SETTINGS_DEFER, $storeId);
    }

    static function canInlineCSS($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::CSS_INLINE, $storeId);
    }

    static function canCssMerge($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::CSS_SETTINGS_MERGE, $storeId);
    }

    static function canCssCompression($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::CSS_SETTINGS_COMPRESSION, $storeId);
    }

    static function canCssGzip($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::CSS_SETTINGS_GZIP, $storeId);
    }
}