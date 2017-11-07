<?php

class Compressor_Image_ServeScale
{
    /**
     * start resizing
     */
    static function process()
    {
        if (!isset($_GET['f'])) {
            //return 404 if no requested file
            return self::notFound();
        }
        //get file dimension
        list($width, $height) = explode('x', $_GET['d']);
        $imagePath = self::prepareImgPath($_GET['f']);
        $resizedImagePath = self::getResizedImagePath($imagePath, $width, $height);
        if (!$resizedImagePath) {
            return self::notFound();
        }
        $pathParts = pathinfo($imagePath);
        header("Content-type: image/" . $pathParts['extension']);
        header('Last-Modified: '. gmdate("D, d M Y H:i:s", filemtime($imagePath)) . " GMT");
        header('Connection: keep-alive');
        echo file_get_contents($resizedImagePath);
    }

    /**
     * get file dir path
     *
     * @param string $img
     *
     * @return string
     */
    static function prepareImgPath($img)
    {
        $baseDir = dirname(dirname(dirname(dirname(__FILE__))));
        $img = str_replace('/po_compressor/' . $_GET['d'], '', $img);
        return $baseDir . $img;
    }

    /**
     * get resized image path
     *
     * @param $imagePath
     * @param $width
     * @param $height
     *
     * @return bool|string
     */
    static function getResizedImagePath($imagePath, $width, $height)
    {
        list($currentWidth, $currentHeight, $type, $attr) = getimagesize($imagePath);
        if ($width == 1) {
            //not need resize by width
            $width = $currentWidth;
        }
        if ($height == 1) {
            //not need resize by height
            $height = $currentHeight;
        }
        $pathParts = pathinfo($imagePath);
        if (!in_array($pathParts['extension'], array('jpg', 'jpeg', 'gif', 'png'))) {
            return false;
        }
        if (!self::canResize($imagePath, $width, $height)) {
            return $imagePath;
        }
        $resizedFilePath = $pathParts['dirname'] . '/' . $pathParts['filename'] . '_' . $width . 'x' . $height . '.' . $pathParts['extension'];
        if (file_exists($resizedFilePath)) {
            return $resizedFilePath;
        }
        if (self::resizeImage($imagePath, $resizedFilePath, $width, $height)) {
            return $resizedFilePath;
        }
        return $imagePath;
    }

    /**
     * check if image can be resized
     *
     * @param $imagePath
     * @param $width
     * @param $height
     *
     * @return bool
     */
    static function canResize($imagePath, $width, $height)
    {
        list($currentWidth, $currentHeight, $type, $attr) = getimagesize($imagePath);
        return $width != $currentWidth || $height != $currentHeight;
    }

    /**
     * resize image
     *
     * @param $filePath
     * @param $resizedFilePath
     * @param $width
     * @param $height
     *
     * @return bool
     */
    static function resizeImage($filePath, $resizedFilePath, $width, $height)
    {
        if (!file_exists($filePath)) {
            return false;
        }
        self::register();
        $imageObj = new Varien_Image($filePath);

        $imageObj->constrainOnly(true);
        $imageObj->keepAspectRatio(true);
        $imageObj->keepFrame(false);
        $imageObj->backgroundColor(array(255, 255, 255));
        $imageObj->keepTransparency(true);
        try {
            $imageObj->resize($width, $height);
            $imageObj->save($resizedFilePath);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * 404 response
     */
    static function notFound()
    {
        header("HTTP/1.0 404 Not Found");
        echo "HTTP/1.0 404 Not Found";
    }

    /**
     * autoload register
     */
    static function register()
    {
        spl_autoload_register(array('Compressor_Image_ServeScale', 'autoload'));
    }

    /**
     * autoload
     *
     * @param $class
     *
     * @return mixed
     */
    static function autoload($class)
    {
        $classFile = str_replace(' ', DIRECTORY_SEPARATOR, ucwords(str_replace('_', ' ', $class)));
        return include dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . $classFile . '.php';
    }
}
Compressor_Image_ServeScale::process();