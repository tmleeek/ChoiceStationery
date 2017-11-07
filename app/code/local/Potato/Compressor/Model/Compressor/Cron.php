<?php

class Potato_Compressor_Model_Compressor_Cron
{
    const CACHE_ID = 'Potato_Compressor_Model_Compressor_Cron::process';
    const CACHE_LIFETIME = 86400;
    const PROCESS_STEP = 25;

    /**
     * start image optimization
     *
     * @return $this
     */
    public function process()
    {
        if (!Potato_Compressor_Helper_Config::isEnabled() ||
            !Potato_Compressor_Helper_Config::isImageCronEnabled()
        ) {
            return $this;
        }

        $imageCollection = $this->_getImageGalleryFiles();
        $counter = 0;
        foreach ($imageCollection as $key => $image) {
            try {
                Mage::getSingleton('po_compressor/compressor_image')->optimizeImage($image);
                $counter++;
                unset($imageCollection[$key]);
                $this->_saveCache($imageCollection);
                if ($counter == self::PROCESS_STEP) {
                    break;
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this;
    }

    /**
     * get images collection
     *
     * @return mixed
     */
    protected function _getImageGalleryFiles()
    {
        $imageCollection = $this->_loadFromCache();
        if ($imageCollection && is_array($imageCollection) && !empty($imageCollection)) {
            return $imageCollection;
        }
        return Mage::helper('po_compressor')->getImageGalleryFiles();
    }

    /**
     * load image collection from cache
     *
     * @return mixed
     */
    protected function _loadFromCache()
    {
        return unserialize(Mage::app()->loadCache(self::CACHE_ID));
    }

    /**
     * save image collection
     *
     * @param $imageCollection
     *
     * @return $this
     */
    protected function _saveCache($imageCollection)
    {
        Mage::app()->saveCache(serialize($imageCollection), self::CACHE_ID, array(), self::CACHE_LIFETIME);
        return $this;
    }
}