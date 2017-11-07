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
class MDN_CrmTicket_Helper_Attachment extends Mage_Core_Helper_Abstract {

    private static $_PUBLIC_ALLOWED_FILE_EXTENSIONS = array( 'png', 'img', 'gif', 'jpg', 'jpeg', 'bmp', 'zip', 'rar', 'csv', 'pdf', 'doc', 'docx', 'txt', 'odt', 'txt', 'log', 'tiff', 'xls', 'xlsx', 'ppt', 'pptx');
    private static $_ADMIN_ALLOWED_FILE_EXTENSIONS  = array( 'png', 'img', 'gif', 'jpg', 'jpeg', 'bmp', 'zip', 'rar', 'csv', 'pdf', 'doc', 'docx', 'txt', 'odt', 'txt', 'log', 'tiff', 'xls', 'xlsx', 'ppt', 'pptx', 'sql');

    private static $_MAX_PUBLIC_ATTACHEMENT_ALLOWED = 3;
    private static $_MAX_ADMIN_ATTACHEMENT_ALLOWED = 3;
    private static $_MAX_ATTACHEMENT_ALLOWED = 10;
    private static $_ATTACHEMENT_CONF_SEPARATOR = ',';
    private static $_NEW_MESSAGE_ATTACHEMENT_KEY = 'new_mess_attachment_';
    private static $_NEW_TICKET_ATTACHEMENT_KEY = 'new_attachment';
    private static $_PUBLIC_ATTACHEMENT_KEY = 'attachment_';

    public function getAdminAllowedFileExtensions() {

        return Mage::helper('CrmTicket')-> getConfTextAreaAsTrimedArray(
                'crmticket/attachements/attachements_type_allowed_back',
                self::$_ATTACHEMENT_CONF_SEPARATOR,
                self::$_ADMIN_ALLOWED_FILE_EXTENSIONS);
    }

    public function getPublicAllowedFileExtensions() {        
        return Mage::helper('CrmTicket')-> getConfTextAreaAsTrimedArray(
                'crmticket/attachements/attachements_type_allowed_front',
                self::$_ATTACHEMENT_CONF_SEPARATOR,
                self::$_PUBLIC_ALLOWED_FILE_EXTENSIONS);
    }

    public function getPublicMaxAttachementAllowed() {
        $defaultvalue = self::$_MAX_PUBLIC_ATTACHEMENT_ALLOWED;
        $confValue = Mage::getStoreConfig('crmticket/attachements/max_attachements_front');
        if(is_numeric($confValue) && $confValue > 0 && $confValue < self::$_MAX_ATTACHEMENT_ALLOWED){
          $defaultvalue = $confValue;
        }
        return $defaultvalue;
    }

    public function getAdminMaxAttachementAllowed() {
        $defaultvalue = self::$_MAX_ADMIN_ATTACHEMENT_ALLOWED;
        $confValue = Mage::getStoreConfig('crmticket/attachements/max_attachements_back');
        if(is_numeric($confValue) && $confValue > 0 && $confValue < self::$_MAX_ATTACHEMENT_ALLOWED){
          $defaultvalue = $confValue;
        }
        return $defaultvalue;
    }

    public function getAdminMessageAttachementKey() {
        return self::$_NEW_MESSAGE_ATTACHEMENT_KEY;
    }

    public function getPublicMessageAttachementKey() {
        return self::$_PUBLIC_ATTACHEMENT_KEY;
    }

    public function getAdminTicketAttachementKey() {
        return self::$_NEW_TICKET_ATTACHEMENT_KEY;
    }

    private static $_MEDIA_FOLDER_NAME = 'media';
    private static $_EXTENSION_FOLDER_NAME = 'CrmTicket';
    private static $_ATTACHEMENTS_FOLDER_NAME = 'Attachments';
    private static $_NEW_MESSAGE_ATTACHEMENTS_FOLDER_NAME = 'temp'; //temporay folder -> to check if really usefull
    protected $_baseAttachmentFolder;
    protected $_ticketsAttachmentFolder;
    protected $_mailsAttachmentFolder;


    /**
     * check if the extension of a file is allowed depending of one allowed extension list
     * 
     * @param type $filename
     * @param type $allowedExtensionList
     * @return boolean
     */
    public function checkAttachmentAllowed($filename, $allowedExtensionList) {
        $allowed = false;
        $pointPos = strrpos($filename, '.');
        if($pointPos>0){
          $ext = strtolower(trim(substr($filename, $pointPos+1)));
          if(strlen($ext)>1){
            foreach ($allowedExtensionList as $allowedExtension){
              if($allowedExtension == $ext){
                $allowed = true;
              }
            }
          }
        }
        return $allowed;
    }

    /**
     * Return base attachment directory
     * @return type
     * TODO :  put in conf the folder names + saved + rename folder if change ?
     */
    private function getBaseAttachmentDirectory() {
        if (!$this->_baseAttachmentFolder) {
            $dir = Mage::getBaseDir(self::$_MEDIA_FOLDER_NAME) . DS . self::$_EXTENSION_FOLDER_NAME . DS;
            if (!is_dir($dir))
                mkdir($dir);
            $this->_baseAttachmentFolder = $dir;
        }
        return $this->_baseAttachmentFolder;
    }

    /**
     * Return directory for attachments for all tickets
     * @param type $ticket
     * @return string
     */
    public function getTicketsAttachmentDirectory() {
        if (!$this->_ticketsAttachmentFolder) {
            $dir = $this->getBaseAttachmentDirectory();
            $dir .= self::$_ATTACHEMENTS_FOLDER_NAME . DS;
            if (!is_dir($dir))
                mkdir($dir);

            $this->_ticketsAttachmentFolder = $dir;
        }
        return $this->_ticketsAttachmentFolder;
    }

    
    /**
     * Return directory for attachments for one ticket
     * @param type $ticket
     * @return string
     */
    public function getTicketAttachmentDirectory($ticket) {
        $dir = $this->getTicketsAttachmentDirectory();
        $dir .= $ticket->getId();
        if (!is_dir($dir))
            mkdir($dir);
        return $dir;
    }

    /**
     * Return directory for attachments for one message for one ticket
     * If teh message is new, the folder name is _NEW_MESSAGE_ATTACHEMENTS_FOLDER_NAME
     * @param type $ticket
     * @return string
     */
    public function getMessageAttachmentDirectory($ticket, $messageid) {
        $dir = $this->getTicketAttachmentDirectory($ticket);
        $messFolder = self::$_NEW_MESSAGE_ATTACHEMENTS_FOLDER_NAME;
        if ($messageid) {
            $messFolder = $messageid;
        }
        $dir .= DS . $messFolder . DS;
        if (!is_dir($dir))
            mkdir($dir);
        return $dir;
    }

    
    /**
     * get the full path for a ticket's or a linked mail's attachement
     */
    public function getAttachmentPath($ticket, $attachmentName) {
        return $this->getTicketAttachmentDirectory($ticket) . $attachmentName;
    }

    /**
     * get the full path for a ticket's or a linked mail's attachement
     */
    public function getMessageAttachmentPath($ticket, $message, $attachmentName) {
        return $this->getMessageAttachmentDirectory($ticket, $message->getId()) . $attachmentName;
    }

    
    /**
     * Return attachments for one ticket
     * @param type $ticket
     */
    public function getAttachments($ticket) {
        if (!$ticket->getId())
            return array();

        $dir = $this->getTicketAttachmentDirectory($ticket);

        return $this->getAttachmentsForFolder($dir, $ticket, null);
    }

    /**
     * Return attachments for one message of one ticket
     *
     * @param type $ticket
     * 
     */
    public function getAttachmentsForMessage($ticket, $message) {
        if (!$ticket->getId())
            return array();

        $messageId = self::$_NEW_MESSAGE_ATTACHEMENTS_FOLDER_NAME;
        if ($message) {
            $messageId = $message->getId(); //trick
        }

        $dir = $this->getMessageAttachmentDirectory($ticket, $messageId);

        return $this->getAttachmentsForFolder($dir, $ticket, $message);
    }

    /**
     * Return attachments for one ticket
     * @param type $ticket
     */
    public function getAttachmentsForFolder($dir, $ticket, $message) {

        //get files
        $files = array();
        $handle = opendir($dir);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                if (is_file($dir . $file)) {
                    $att = Mage::getModel('CrmTicket/Attachment');
                    $att->setFilePath($file);
                    $att->setFileName($file);
                    $att->setFullFilePath($dir.$file);
                    $att->setTicket($ticket);
                    if ($message) {
                        $att->setMessage($message);
                    }
                    $files[] = $att;
                }
            }
        }
        closedir($handle);
        //echo "There is ".sizeof($files)." attachements in $dir<br>";
        return $files;
    }

    /**
     * Delete one attachment for one ticket
     * @param boolean s
     */
    public function deleteAttachment($ticket, $filename) {
        $status = false;
        $attachementPath = $this->getAttachmentPath($ticket, $filename);
        if ($attachementPath) {
            $status = unlink($attachementPath);
        }
        return $status;
    }
    
    /**
     * Delete one attachment for one message
     */
    public function deleteMessageAttachment($ticket, $message, $filename) {
        $status = false;
        $attachementPath = $this->getMessageAttachmentPath($ticket, $message, $filename);
        if ($attachementPath) {
            $status = unlink($attachementPath);
        }
        return $status;
    }


    /**
     * delete all attachement for one ticket
     * @param type $ticket
     * @return type
     */
    public function deleteAttachments($ticketId){

        $status = false;
        $attachementPath = $this->getTicketsAttachmentDirectory().$ticketId.DS;
        try{
          if ($attachementPath) {
              $this->recursiveDelete($attachementPath);
              $status=true;
          }
        }catch (Exception $ex){
          //ignore
        }
        return $status;
    }

    /**
     * Delete a file or recursively delete a directory
     *
     * @param string $str Path to file or directory
     */
    public function recursiveDelete($str){
        if(is_file($str)){
            return @unlink($str);
        }
        elseif(is_dir($str)){
            $scan = glob(rtrim($str,'/').'/*');
            foreach($scan as $index => $path){
                $this->recursiveDelete($path);
            }
            return @rmdir($str);
        }
    }

    

    public function getFormattedFilenameForSaving($name) {
        return date('Y-m-d_H') . 'h' . date('i') . '_' . $this->preventFromCodeInjection($name);
    }

    /*
     * preventFromCodeInjection
     *
     * Minimum & Classic security on a public website (even if you need to be loggued to use it)
     * Prevent also bad file naming (like # in the name, that block the download)
     * @param string $name name of the file or text to securize
     */

    public function preventFromCodeInjection($name) {
        if ($name != null && strlen($name) > 0) {
            $name = trim($name);
            $name = strtr($name, 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ', 'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
            $name = preg_replace('/([^._a-z0-9]+)/i', '-', $name);
        } else {
            $name = '';
        }
        return $name;
    }

    /**
     * 
     * @param type $message
     * @param type $attachments
     */
    public function saveAttachments($message, $attachments) {
        $debug = array();
        $ticketAttachmentDirectory = $this->getMessageAttachmentDirectory($message->getTicket(), $message->getId());
        foreach ($attachments as $attachmentName => $attachmentContent) {

            $newFileName = $this->getFormattedFilenameForSaving($attachmentName);
            $filePath = $ticketAttachmentDirectory . $newFileName;

            if (file_exists($filePath))
                unlink($filePath);

            if (file_put_contents($filePath, $attachmentContent)) {
                $debug[] = "<br>Attachment $newFileName : saved successfully into $filePath<br>";
            } else {
                $debug[] = "<br>Failed to save attachment $newFileName into $filePath<br>";
            }
        }
    }

}
