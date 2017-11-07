<?php

class Potato_Compressor_Model_Design_Package extends Mage_Core_Model_Design_Package
{
    /**
     * Prepare url for css replacement
     *
     * @param string $uri
     * @return string
     */
    protected function _prepareUrl($uri)
    {
        $uri = parent::_prepareUrl($uri);
        $uri = str_replace('http:','', $uri);
        $uri = str_replace('https:','', $uri);
        return $uri;
    }

    public function getMergedJsUrl($files)
    {
        $targetFilename = md5(implode(',', $files)) . '.js';
        $filePath = Potato_Compressor_Helper_Data::MAIN_FOLDER . DS . Mage::app()->getStore()->getId() . DS . 'js';
        $targetDir = $this->_initMergerDir($filePath);
        if (!$targetDir) {
            return '';
        }
        $mergeFilesResult = true;
        if (!file_exists($targetDir . DS . $targetFilename)) {
            $mergeFilesResult = $this->_mergeFiles($files, $targetDir . DS . $targetFilename, false,
                array($this, 'beforeMergeJs'), 'js'
            );
        }
        if ($mergeFilesResult) {
            $filePath = str_replace('\\', '/', $filePath);
            return Mage::getBaseUrl('media', Mage::app()->getRequest()->isSecure()) . $filePath . '/' . $targetFilename;
        }
        return '';
    }

    /**
     * @param array  $files
     *
     * @return string
     */
    public function getMergedCssUrl($files)
    {
        // secure or unsecure
        $isSecure = Mage::app()->getRequest()->isSecure();
        $mergerDir = $isSecure ? 'css_secure' : 'css';
        $filePath = Potato_Compressor_Helper_Data::MAIN_FOLDER . DS . Mage::app()->getStore()->getId() . DS . $mergerDir;
        $targetDir = $this->_initMergerDir($filePath);
        if (!$targetDir) {
            return '';
        }

        // base hostname & port
        $baseMediaUrl = Mage::getBaseUrl('media', $isSecure);
        $hostname = parse_url($baseMediaUrl, PHP_URL_HOST);
        $port = parse_url($baseMediaUrl, PHP_URL_PORT);
        if (false === $port) {
            $port = $isSecure ? 443 : 80;
        }

        // merge into target file
        $targetFilename = md5(implode(',', $files) . "|{$hostname}|{$port}") . '.css';
        $mergeFilesResult = true;
        if (!file_exists($targetDir . DS . $targetFilename)) {
            $mergeFilesResult = $this->_mergeFiles(
                $files, $targetDir . DS . $targetFilename,
                false,
                array($this, 'beforeMergeCss'),
                'css'
            );
        }
        if ($mergeFilesResult) {
            $filePath = str_replace('\\', '/', $filePath);
            return $baseMediaUrl . $filePath . '/' . $targetFilename;
        }
        return '';
    }

    protected function _mergeFiles(array $srcFiles, $targetFile = false,
        $mustMerge = false, $beforeMergeCallback = null, $extensionsFilter = array())
    {
        $srcFiles = $this->_parseFilePath($srcFiles);
        $result = Mage::helper('core')->mergeFiles(
            $srcFiles,
            false,
            $mustMerge,
            $beforeMergeCallback,
            $extensionsFilter
        );
        $pathInfo = pathinfo($targetFile);
        if ($extensionsFilter == Potato_Compressor_Model_Compressor_Css::FILE_EXTENSION &&
            Potato_Compressor_Helper_Config::canCssCompression()
        ) {
            $result = Mage::getSingleton('po_compressor/compressor_css')->compression($result);

            if (Potato_Compressor_Helper_Config::canCssGzip()) {
                Potato_Compressor_Helper_Data::createHtaccessFile($pathInfo['dirname']);
            }
        } elseif ($extensionsFilter == Potato_Compressor_Model_Compressor_Js::FILE_EXTENSION &&
            Potato_Compressor_Helper_Config::canJsCompression()
        ) {
            $result = Mage::getSingleton('po_compressor/compressor_js')->compression($result);
            if (Potato_Compressor_Helper_Config::canJsGzip()) {
                Potato_Compressor_Helper_Data::createHtaccessFile($pathInfo['dirname']);
            }
        }
        file_put_contents($targetFile, $result, LOCK_EX);
        return true;
    }

    public function beforeMergeJs($file, $contents)
    {
        $contents = rtrim($contents, ';') . ';';
        return $contents;
    }

    protected function _parseFilePath($srcFiles)
    {
        $result = array();
        foreach ($srcFiles as $filePath) {
            $filePath = explode('?', $filePath);
            $result[] = $filePath[0];
        }
        return $result;
    }
}