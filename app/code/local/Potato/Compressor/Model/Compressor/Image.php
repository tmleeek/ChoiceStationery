<?php

class Potato_Compressor_Model_Compressor_Image
{
    const JPEG_WIN_FILENAME = 'jpegoptim.exe';
    const JPEG_UNIX_FILENAME = 'jpegoptim';

    const GIF_WIN_FILENAME = 'gifsicle.exe';
    const GIF_UNIX_FILENAME = 'gifsicle';

    const PNG_WIN_FILENAME = 'optipng.exe';
    const PNG_UNIX_FILENAME = 'optipng';

    const MEDIA_ORIGINAL_FOLDER_NAME = 'media_original_images';
    const SKIN_ORIGINAL_FOLDER_NAME = 'skin_original_images';

    const TRANSPARENT_IMG_BASE64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGP6zwAAAgcBApocMXEAAAAASUVORK5CYII=';

    public function optimizeImage($image)
    {
        if (!$this->_getIsCanOptimized($image)) {
            return true;
        }
        $optimizedImage = Mage::getModel('po_compressor/image')->loadByHash($this->_getImageHash($image));
        if ($optimizedImage->getId()) {
            return true;
        }
        if (Mage::helper('po_compressor/config')->isAllowImageBackup()) {
            $this->_backupImage($image);
        }
        $winLibPath = BP . DS . 'lib' . DS . 'Compressor' . DS . 'img_optimization_tools' . DS  . 'win32';
        $unixLibPath = BP . DS . 'lib' . DS . 'Compressor' . DS . 'img_optimization_tools' . DS  . 'unix';
        $unix64LibPath = BP . DS . 'lib' . DS . 'Compressor' . DS . 'img_optimization_tools' . DS  . 'unix' . DS . '64';
        $info = getimagesize($image);
        $_result = array();
        switch($info[2]) {
            case 1:
            case 3:
                if ($info[2] == 3 || $info[2] == 1 && !$this->_isAnimatedGif($image)) {
                    //PNG or GIF without animate
                    $libPath = $unixLibPath . DS . self::PNG_UNIX_FILENAME;
                    if ($this->_getIsWinOs()) {
                        $libPath = $winLibPath . DS . self::PNG_WIN_FILENAME;
                    } else if($this->_is64bit()) {
                        $libPath = $unix64LibPath . DS . self::PNG_UNIX_FILENAME;
                    }
                    $pngFileName = dirname($image) . DS . basename($image, ".gif") . '.png';
                    if ($info[2] == 1 && file_exists($pngFileName)) {
                        //after optimization img may be renamed to .png -> need do backup if same file already exists
                        rename($pngFileName, $pngFileName . '_tmp');
                    }
                    exec($libPath . ' -o7 ' . $image, $_result);
                    if ($info[2] == 1 && file_exists($pngFileName)) {
                        rename($pngFileName, $image);
                    }
                    if ($info[2] == 1 && file_exists($pngFileName . '_tmp')) {
                        //restore previously renamed image
                        rename($pngFileName . '_tmp', $pngFileName);
                    }
                    break;
                }
                //GIF with animate
                $libPath = $unixLibPath . DS . self::GIF_UNIX_FILENAME;
                if ($this->_getIsWinOs()) {
                    $libPath = $winLibPath . DS . self::GIF_WIN_FILENAME;
                } else if($this->_is64bit()) {
                    $libPath = $unix64LibPath . DS . self::GIF_UNIX_FILENAME;
                }
                exec($libPath . ' -b -O3 ' . $image, $_result);
                break;
            case 2:
                //JPG
                $libPath = $unixLibPath . DS . self::JPEG_UNIX_FILENAME;
                if ($this->_getIsWinOs()) {
                    $libPath = $winLibPath . DS . self::JPEG_WIN_FILENAME;
                } else if($this->_is64bit()) {
                    $libPath = $unix64LibPath . DS . self::JPEG_UNIX_FILENAME;
                }
                exec($libPath . ' -f -o --strip-all --strip-icc --strip-iptc -m100 ' . $image, $_result);
                break;
        }
        $optimizedImage->setHash($this->_getImageHash($image))->save();
        Mage::log(print_r($_result,true),1,'po_cmp.log', true);
        return true;
    }

    public function replaceImageUrl($response)
    {
        $body = $response->getBody();
        //get all img
        preg_match_all('/<img.*src=("[^"]+").*>/', $body, $matches);
        if (empty($matches)) {
            return $body;
        }
        foreach ($matches[0] as $imageTag) {
            //check ignore
            preg_match('@po_cmp_ignore@', $imageTag, $match);
            if (!empty($match)) {
                continue;
            }
            //get image url
            if (!$imageUrl = $this->_parseImageUrl($imageTag)) {
                continue;
            }
            //check image is media or skin
            if (!Potato_Compressor_Helper_Data::isMediaImage($imageUrl) && !Potato_Compressor_Helper_Data::isSkinImage($imageUrl)) {
                continue;
            }
            //remove src attribute
            $newImageTag = str_replace($imageUrl, self::TRANSPARENT_IMG_BASE64, $imageTag);
            if (Potato_Compressor_Helper_Data::isSkinImage($imageUrl)) {
                $type = 'skin';
                $imageUrl = 'skin/' . str_replace(Mage::getBaseUrl('skin', Mage::app()->getRequest()->isSecure()), '', $imageUrl);
            } else {
                $type = 'media';
                $imageUrl = 'media/' . str_replace(Mage::getBaseUrl('media', Mage::app()->getRequest()->isSecure()), '', $imageUrl);
            }
            $newImageTag = str_replace('<img', '<img onload="serveScaledImage(this, \'' . $imageUrl . '\', \'' . $type . '\')"', $newImageTag);
            $body = str_replace($imageTag, $newImageTag, $body);
        }
        $response->setBody($body);
        return $this;
    }

    protected function _parseImageUrl($imageTag)
    {
        preg_match_all('/src="([^"]+)"/', $imageTag, $matches);
        if (empty($matches)) {
            return false;
        }
        return $matches[1][0];
    }

    protected function _getIsCanOptimized($image)
    {
        $path = str_replace(BP . DS, '', $image);
        if (!in_array($path, Potato_Compressor_Helper_Config::getSkippedImages())) {
            return true;
        }
        if (Mage::helper('po_compressor/config')->isAllowImageBackup()) {
            $this->_restoreImage($image);
        }
        return false;
    }

    protected function _restoreImage($image)
    {
        $backupImg = $this->_getBackupImagePath($image);
        if ($backupImg && file_exists($backupImg)) {
            $content = file_get_contents($backupImg);
            file_put_contents($image, $content);
        }
        return $this;
    }

    protected function _is64bit() {
        $int = "9223372036854775807";
        $int = intval($int);
        if ($int == 9223372036854775807) {
            /* 64bit */
            return true;
        }
        return false;
    }

    protected function _getBackupImagePath($image)
    {
        $path = false;
        if (strpos($image, Mage::getBaseDir('media')) !== FALSE) {
            $path = Mage::getBaseDir('media') . DS . self::MEDIA_ORIGINAL_FOLDER_NAME
                . DS . str_replace(Mage::getBaseDir('media'), '', $image)
            ;
        }
        if (!$path && strpos($image, Potato_Compressor_Helper_Data::getSkinDir()) !== FALSE) {
            $path = Potato_Compressor_Helper_Data::getSkinDir() . DS . self::SKIN_ORIGINAL_FOLDER_NAME
                . DS . str_replace(Potato_Compressor_Helper_Data::getSkinDir(), '', $image)
            ;
        }
        return $path;
    }

    protected function _backupImage($image)
    {
        $path = str_replace(BP . DS, '', $this->_getBackupImagePath($image));
        if (isset($path)) {
            $rootPath = BP;
            foreach (explode(DS, $path) as $target) {
                $rootPath .= DS . $target;
                if (file_exists($rootPath)) {
                    continue;
                }
                $info = pathinfo($rootPath);
                if (array_key_exists('extension', $info) && $info['extension'] != '') {
                    $content = file_get_contents($image);
                    file_put_contents($rootPath, $content);
                    break;
                }
                Potato_Compressor_Helper_Data::prepareFolder($rootPath);
            }
        }
        return $this;
    }

    protected function _getIsWinOs()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            return false;
        }
        return true;
    }

    protected function _getImageHash($image)
    {
        return md5($image . file_get_contents($image));
    }

    protected function _isAnimatedGif($image)
    {
        $content = file_get_contents($image);
        $strLoc = 0;
        $count = 0;

        // There is no point in continuing after we find a 2nd frame
        while ($count < 2)
        {
            $where1 = strpos($content, "\x00\x21\xF9\x04", $strLoc);
            if ($where1 === FALSE) {
                break;
            }
            $str_loc = $where1+1;
            $where2  = strpos($content, "\x00\x2C", $str_loc);
            if ($where2 === FALSE) {
                break;
            } else {
                if ($where1 + 8 == $where2) {
                    $count++;
                }
                $strLoc = $where2 + 1;
            }
        }
        // gif is animated when it has two or more frames
        return ($count >= 2);
    }
}