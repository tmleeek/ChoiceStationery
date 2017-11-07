<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 BoostMyshop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Model_Attachment extends Mage_Core_Model_Abstract {

    /**
     * Check if attachment can be previewed
     */
    public function canPreview() {
        return $this->isPicture($this->getFileName());
    }

    /**
     * return true if the attachment is a Picture type
     */
    public function isPicture($attachmentName) {
        $attachmentName = strtolower($attachmentName);
        return (strpos($attachmentName, '.png') > 0
                || strpos($attachmentName, '.jpg') > 0
                || strpos($attachmentName, '.jpeg') > 0
                || strpos($attachmentName, '.gif') > 0
                || strpos($attachmentName, '.bmp') > 0);
    }

    /**
     * Return attachment content
     * @return string
     */
    public function getContent() {
        if (file_exists($this->getFullFilePath()))
            return file_get_contents($this->getFullFilePath());
        else
            return '';
    }

    /**
     * 
     */
    public function getContentType() {
        $filePath = $this->getFullFilePath();
        if ($filePath)
        {
            return mime_content_type($filePath);
        }
        else
        {
            return null;
        }
    }

}

?>
