<?php

class Potato_Compressor_Helper_Data extends Mage_Core_Helper_Abstract
{
    const MAIN_FOLDER = 'po_compressor';

    public function getRootCachePath()
    {
        return Mage::getBaseDir('media') . DS. self::MAIN_FOLDER;
    }

    public function getRootCacheUrl()
    {
        return Mage::getBaseUrl('media') . self::MAIN_FOLDER;
    }

    public function clearCache()
    {
        $this->_removeFolder($this->getRootCachePath());
        return $this;
    }

    protected function _removeFolder($dirPath)
    {
        Varien_Io_File::rmdirRecursive($dirPath);
        return true;
    }

    public function getImageGalleryFiles()
    {
        return array_merge($this->_getImagesFromDir(Mage::getBaseDir('media')),
            $this->_getImagesFromDir(self::getSkinDir())
        );
    }

    static function getSkinDir()
    {
        return Mage::getBaseDir('skin') . DS . 'frontend';
    }

    protected function _getImagesFromDir($dirPath)
    {
        $findedFiles = array_diff(scandir($dirPath),
            array (
                '..',
                '.',
                '.htaccess',
                Potato_Compressor_Model_Compressor_Image::MEDIA_ORIGINAL_FOLDER_NAME,
                Potato_Compressor_Model_Compressor_Image::SKIN_ORIGINAL_FOLDER_NAME
            )
        );
        $_result = array();
        foreach ($findedFiles as $file) {
            if (is_dir($dirPath . DS . $file)) {
                $_result = array_merge($_result, $this->_getImagesFromDir($dirPath . DS . $file));
                continue;
            }
            if (!@getimagesize($dirPath . DS . $file)) {
                continue;
            }
            array_push($_result, $dirPath . DS . $file);
        }
        return $_result;
    }

    static function prepareFolder($folderPath)
    {
        return Mage::getConfig()->createDirIfNotExists($folderPath);
    }

    static function createHtaccessFile($path)
    {
        $content = "<ifmodule mod_deflate.c>\n"
            . "AddOutputFilterByType DEFLATE text/html text/plain text/css application/json\n"
            . "AddOutputFilterByType DEFLATE application/javascript\n"
            . "AddOutputFilterByType DEFLATE text/xml application/xml text/x-component\n"
            . "AddOutputFilterByType DEFLATE application/xhtml+xml application/rss+xml application/atom+xml\n"
            . "AddOutputFilterByType DEFLATE image/svg+xml application/vnd.ms-fontobject application/x-font-ttf font/opentype\n"
            . "SetOutputFilter DEFLATE\n"
            . "</ifmodule>\n"
            . "<ifmodule mod_headers.c>\n"
            . "<FilesMatch '\.(css|js|jpe?g|png|gif)$'>\n"
            . "Header set Cache-Control 'max-age=2592000, public'\n"
            . "</FilesMatch>\n"
            . "</ifmodule>\n"
            . "<ifmodule mod_expires.c>\n"
            . "ExpiresActive On\n"
            . "ExpiresByType text/css 'access plus 30 days'\n"
            . "ExpiresByType text/javascript 'access plus 30 days'\n"
            . "ExpiresByType application/x-javascript 'access plus 30 days'\n"
            . "ExpiresByType image/jpeg 'access plus 30 days'\n"
            . "ExpiresByType image/png 'access plus 30 days'\n"
            . "ExpiresByType image/gif 'access plus 30 days'\n"
            . "</ifmodule>\n"
        ;
        file_put_contents($path . DS . '.htaccess', $content);
        return true;
    }

    static function minifyContent($content)
    {
        if (!Potato_Compressor_Helper_Config::isEnabled() ||
            !Potato_Compressor_Helper_Config::getIsCanMinifyHtml() ||
            strpos($content, 'html') === false) {
            return $content;
        }
        if (!@class_exists('Minify_HTMLMax')) {
            set_include_path( BP.DS.'lib'.DS.'Minify'. DS . 'Minify' .PS . get_include_path());
            require_once 'HTMLMax.php';
        }
        if (!@class_exists('JSMin')) {
            set_include_path( BP.DS.'lib'.DS.'Minify'. PS . get_include_path());
            require_once 'JSMin.php';
        }
        if (!@class_exists('Minify_CSS')) {
            set_include_path( BP.DS.'lib'.DS.'Minify'. DS . 'Minify' .PS . get_include_path());
            require_once 'CSS.php';
        }
        $content = Minify_HTMLMax::minify($content,
            array(
                'cssMinifier' => array('Minify_CSS', 'minify'),
                'jsMinifier'  => array('JSMin', 'minify')
            )
        );
        return $content;
    }

    static function isMediaImage($image)
    {
        return strpos($image, Mage::getBaseUrl('media', Mage::app()->getRequest()->isSecure())) !== FALSE;
    }

    static function isSkinImage($image)
    {
        return strpos($image, Mage::getBaseUrl('skin', Mage::app()->getRequest()->isSecure())) !== FALSE;
    }
}