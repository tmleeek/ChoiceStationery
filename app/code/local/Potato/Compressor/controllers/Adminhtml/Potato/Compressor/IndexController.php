<?php

require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'System' . DS . 'ConfigController.php';
class Potato_Compressor_Adminhtml_Potato_Compressor_IndexController extends Mage_Adminhtml_System_ConfigController
{
    public function optimizationAction()
    {
        $session = Mage::getSingleton('adminhtml/session');
        $response = array('progress' => 0);
        if(!function_exists('exec')) {
            $session->addError(Mage::helper('po_compressor')->__('Can\'t run process, please enable "exec" function'));
            $response['reload'] = 1;
            $response = Mage::helper('core')->jsonEncode($response);
            $this->getResponse()->setBody($response);
            return $this;
        }
        if (null === $session->getPoCompressorData()) {
            $compressorData = new Varien_Object;
            $compressorData->setImageGallery(Mage::helper('po_compressor')->getImageGalleryFiles());
            $compressorData->setImageGalleryCount(count($compressorData->getImageGallery()));
            $session->setPoCompressorData($compressorData);
        } else {
            $compressorData = $session->getPoCompressorData();
            $imageGallery = $compressorData->getImageGallery();
            $counter = 0;
            foreach ($imageGallery as $key => $image) {
                try {
                    Mage::getSingleton('po_compressor/compressor_image')->optimizeImage($image);
                } catch (Exception $e) {
                    $session->addException($e,
                        Mage::helper('adminhtml')->__('An error occurred while saving this configuration:') . ' '
                        . $e->getMessage())
                    ;
                    $response['reload'] = 1;
                    $response = Mage::helper('core')->jsonEncode($response);
                    $this->getResponse()->setBody($response);
                    return $this;
                }
                $counter++;
                unset($imageGallery[$key]);
                if ($counter == 5) {
                    break;
                }
            }
            $compressorData->setImageGallery($imageGallery);
            $session->setPoCompressorData($compressorData);
            $response['progress'] = 100 - floor((count($compressorData->getImageGallery()) / $compressorData->getImageGalleryCount()) * 100);
            if (count($compressorData->getImageGallery()) == 0) {
                $session->setPoCompressorData(null);
                $session->addSuccess(Mage::helper('po_compressor')->__('Image Optimization complete.'));
                $response['reload'] = 1;
            }
        }
        $response = Mage::helper('core')->jsonEncode($response);
        $this->getResponse()->setBody($response);
        return $this;
    }

    public function flushAction()
    {
        try {
            Mage::getResourceModel('po_compressor/image')->truncate();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('po_compressor')->__('The Compressor Images Cache has been cleaned.'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirectReferer();
    }
}