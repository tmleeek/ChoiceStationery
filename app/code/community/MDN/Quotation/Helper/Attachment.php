<?php

class MDN_Quotation_Helper_Attachment extends Mage_Core_Helper_Abstract {

    /**
     * return attachment directory
     *
     * @return unknown
     */
    public function getAttachmentDirectory() {
        $path = Mage::getBaseDir('media') . "/quotation_attachment/";

        //if directory doesn't exist
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }

    public function getAttachmentPath($quote)
    {
        $path = $this->getAttachmentDirectory();
        $fileName = $this->getFileName($quote);
        return $path.$fileName;
    }

    public function getFileName($quote)
    {
        $fileName = $quote->getId().'.pdf';
        return $fileName;
    }

    public function attachmentExists($quote)
    {
        $path = $this->getAttachmentPath($quote);
        return file_exists($path);
    }
}