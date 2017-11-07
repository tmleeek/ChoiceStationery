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
class MDN_CrmTicket_Model_Observer {

    /**
     * Retrieve mail from all mail box with an activated account
     * 
     */
    public function checkAllActivatedMailAccount() {
        $debug = array();

        $debug[] = 'Begin CRON checkAllActivatedMailAccount at '.date('Ymdhis');

        try
        {
          $collection = Mage::getModel('CrmTicket/EmailAccount')
                ->getCollection()
                ->addFieldToFilter('cea_enabled', 1)
                ->setOrder('cea_connection_type', 'asc');
          
          foreach($collection as $emailAccountItem){

            $ceaId = $emailAccountItem->getcea_id();
            $emailAccount = Mage::getModel('CrmTicket/EmailAccount')->load($ceaId);

            //to avoid to block other accounts
            try
            {
              $result = Mage::getModel('CrmTicket/Email_Main')->checkForMails($emailAccount);
            }
            catch(Exception $ex)
            {
              //disable email account for cron if an error occurs !
              //$emailAccount->setcea_enabled(0);
              //$debug[] = "DISABLE ".$emailAccount->getcea_name()." because connection crash";
              //$emailAccount->save();
              $debug[] = "checkAllActivatedMailAccount Exception: ".$ex->getMessage();
            }

            $debug[] = $result;
          }
        }
        catch(Exception $ex)
        {
          $debug[] = "checkAllActivatedMailAccount Exception: ".$ex->getMessage();
        }
        
        $debug[] = 'End CRON checkAllActivatedMailAccount at '.date('Ymdhis');
        Mage::helper('CrmTicket')->log(implode("\n", $debug));
    }

    /**
     * Clean older thatn nb of month define in conf
     *
     */
    public function cleanOldEmails()
    {
       $debug = array();

       $debug[] = 'Begin CRON cleanOldEmails at '.date('Y-m-d h.i.s');

       $emailDeleted = 0;
       $nbmonth = Mage::getStoreConfig('crmticket/pop/delete_mail_month');

        if($nbmonth>0){

          if($nbmonth>11){
            $nbmonth = 11; //limit to avoid bad processing
          }

          $collection = Mage::getModel('CrmTicket/Email')->getCollection();

          $emailCount = $collection->getSize();
          $debug[] = $emailCount.' emails in database';
          
          if($emailCount>0){

            $limitTimeStamp = mktime(0, 0, 0, date("m")-$nbmonth, date("d"),   date("Y"));

            foreach ($collection as $mail) {
              $emailDate = $mail->getctm_date();
              if($emailDate){
                $emailTimeStamp = strtotime($emailDate);
                if($emailTimeStamp<$limitTimeStamp){
                  $debug[] = 'delete email'.$mail->getId().' with date ='.$emailDate;
                  $mail->delete();
                  $emailDeleted++;
                }
              }
            }
          }
        }else{
          $debug[] = 'The setting in crmticket/pop/delete_mail_month is not valid :'.$nbmonth;
        }
        $debug[] = $emailDeleted.' emails deleted';

        $debug[] = 'End CRON cleanOldEmails at '.date('Y-m-d h.i.s');
        Mage::helper('CrmTicket')->log(implode("\n", $debug));

        return $emailDeleted;
    }
}


