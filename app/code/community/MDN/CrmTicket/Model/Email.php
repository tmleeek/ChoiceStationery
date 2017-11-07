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
class MDN_CrmTicket_Model_Email extends Mage_Core_Model_Abstract {
    //EMAIL Object

    const STATUS_NEW = 'new';
    const STATUS_ASSOCIATED = 'associated';
    const STATUS_IGNORED = 'ignored';
    const STATUS_ERROR = 'error';
    const STATUS_SPAM = 'spam';

    public function _construct() {
        $this->_init('CrmTicket/Email', 'ctm_id');
    }

    /**
     * Get all statuses
     * @return array
     */
    public function getStatuses()
    {
        $t = array();
        $t[self::STATUS_NEW] = Mage::helper('CrmTicket')->__(self::STATUS_NEW);
        $t[self::STATUS_ASSOCIATED] = Mage::helper('CrmTicket')->__(self::STATUS_ASSOCIATED);
        $t[self::STATUS_IGNORED] = Mage::helper('CrmTicket')->__(self::STATUS_IGNORED);
        $t[self::STATUS_ERROR] = Mage::helper('CrmTicket')->__(self::STATUS_ERROR);
        
        return $t;
    }
    
    /*
     * Try to parse a mail from a saved Raw content
     */
    public function extractInfosFromRawMail() {

        $mailParser = Mage::getModel('CrmTicket/Email_MailParser');        
        $structuredEmail = $mailParser->parse($this->getctm_rawheader(),$this->getctm_rawcontent());
       
        
        return $structuredEmail;
    }

    /**
     * Convert email to ticket (and save ticket)
     * Ability to ignore Spam
     */
    public function convertToTicket($ignoreSpam = false) {
        
        try 
        {
            $mail = $this->extractInfosFromRawMail();            
            $ticket = null;
            
            if (!$mail)
                throw new Exception('Unable to convert raw mail to structured email');

            $mailToProcess = true;
            
            if(!$ignoreSpam){
              if ($mail->isSpam())
              {
                  $this->setctm_status(self::STATUS_SPAM);
                  $this->setctm_status_message('Mail is a spam !');
                  $mailToProcess =false;
              }
            }
           
            if ($mailToProcess)
            {
                $ticket = Mage::getModel('CrmTicket/Email_EmailToTicket')->convert($mail,$this);
                if ($ticket)
                {
                    $this->setctm_status(self::STATUS_ASSOCIATED);
                    $this->setctm_ticket_id($ticket->getId());

                    //save email account into ticket
                    $ticket->setct_email_account($this->getctm_account());
                    $ticket->save();
                }
                else
                {
                    $this->setctm_status(self::STATUS_IGNORED);
                }
                $this->setctm_status_message('');
            }
            
            //update subject & from email
            $this->setctm_from_email($mail->fromEmail);
            $this->setctm_subject($mail->subject);

            
            $this->save();

            return $ticket;
        }
        catch(Exception $ex)
        {
            $this->setctm_status(self::STATUS_ERROR);
            $this->setctm_status_message($ex->getMessage());
            $this->save();
            
            Mage::helper('CrmTicket')->logErrors('Unable to convert email #'.$this->getId().' : '.$ex->getMessage());
            
            return null;
        }
        
    }
    
    /**
     * Convert new emails to ticket (button on Mail box)
     */
    public function processNewEmails()
    {
        $count = 0;
        $limit = 500;        

        $collection = Mage::getModel('CrmTicket/Email')
                ->getCollection()
                ->addFieldToFilter('ctm_ticket_id', 0)
                ->addFieldToFilter('ctm_status', MDN_CrmTicket_Model_Email::STATUS_NEW);
        foreach ($collection as $mail) {
            try {
                $mail->convertToTicket();
                $count++;
                if ($count > $limit)
                    break;
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }
        }
        
        return $count;
    }
    
    /**
     * After save
     */
    protected function _afterSave() {
        parent::_afterSave();
       
        
        //if the MAIL's status is new -> convert email to ticket
        if ($this->getctm_status() == MDN_CrmTicket_Model_Email::STATUS_NEW)
            $this->convertToTicket();
      
        //if the MAIL's status is error -> notify technical contact
        if ($this->getctm_status() == self::STATUS_ERROR)
            Mage::helper('CrmTicket')->notifyTechnicalContact('Error converting email #'.$this->getId());        
        
    }

}