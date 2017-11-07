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
class MDN_CrmTicket_Model_EmailRouterRules_StandardRulesSet extends Mage_Core_Model_Abstract {

    /**
     * Entry point
     *
     * Try to apply active routing rules to ticket depending of the email recieved
     *
     * @param type $email
     * @param type $ticket
     */
    public function updateTicketUsingRules($email, $ticket)
    {
      $rulesToExecute = $this->getRulesToExecuteByPriority($email);
      return $this->executeRulesOnTicket($rulesToExecute, $ticket);
    }

    protected function executeRulesOnTicket($rulesToExecute, $ticket){

      $nbUpdate = 0;

      if($rulesToExecute){

        foreach($rulesToExecute as $rule){
          $updated = false;

          $storeId = $rule->getcerr_store_id();
          if($storeId != 0){
            $ticket->setct_storeid($storeId);
            //echo "<br/>Store changed to ".$storeId." for tid=".$ticket->getId();
            $updated = true;
            $nbUpdate ++;
          }

          $managerId = $rule->getcerr_manager_id();
          if($managerId != 0){
            $ticket->setct_manager($managerId);
            //echo "<br/>Manager changed to ".$managerId." for tid=".$ticket->getId();
            $updated = true;
            $nbUpdate ++;
          }

          $statusLabel = $rule->getcerr_status();
          if(strlen($statusLabel)>0){
            if($statusLabel != "0"){
              $ticket->setct_status($statusLabel);
              //echo "<br/>Status changed to ".$statusLabel." for tid=".$ticket->getId();
              $updated = true;
              $nbUpdate ++;
            }
            
          }

          $categoryId = $rule->getcerr_category_id();
          if($categoryId != 0){
            $ticket->setct_category_id($categoryId);
            //echo "<br/>Category changed to ".$categoryId." for tid=".$ticket->getId();
            $updated = true;
            $nbUpdate ++;
          }

          if($updated){
            $ticket->save();
          }
        }
      }

      return $nbUpdate;
    }


    protected function getRulesToExecuteByPriority($email){

      $rulesIdsToExecute = $this->getMatchingRules($email->to, $email->fromEmail, $email->subject, $email->response);

      //echo "<br>Rules to executes:";
      //var_dump($rulesIdsToExecute);

      $collection = null;

      if(count($rulesIdsToExecute)>0){
        $collection = Mage::getModel('CrmTicket/EmailRouterRules')
                  ->getCollection()
                  ->addFieldToFilter('cerr_id', array("in" => $rulesIdsToExecute))
                  ->setOrder('cerr_priority', 'desc');//desc, because rule of priority 1 have to be executed after rules of priority 2
                  //TODO add a second criteria when 2 rules have the same priority
      }

      return $collection;
    }


    /**
     * Define if an email have rules to be applied
     *
     * @param type $emailAccount
     * @param type $fromEmail
     * @param type $subject
     * @return type
     */
    private function getMatchingRules($emailAccount, $fromEmail, $subject, $body){

        //1st we check email account
        $emailAccountRulesIds = $this->getActivesRulesByEmailAccount($emailAccount);
    
        //2nd we check "From" matching
        $fromRulesIds = $this->getActivesRulesByFromPattern($fromEmail);
   
        //3rd we check "Subject" account
        $subjectRulesIds = $this->getActivesRulesBySubjectPattern($subject);
       
        //4nd we check "Body" account
        $bodyRulesIds = $this->getActivesRulesByBodyPattern($body);
     
        $rulesIdsToExecute = array_merge($emailAccountRulesIds, $fromRulesIds);
        $rulesIdsToExecute = array_merge($rulesIdsToExecute, $subjectRulesIds);
        $rulesIdsToExecute = array_merge($rulesIdsToExecute, $bodyRulesIds);

        return $rulesIdsToExecute;
    }


    private function getActivesRulesByBodyPattern($body){

        $rulesId = array();
      
        $body = Mage::helper('CrmTicket/String')->normalize($body);
        
        if($body){
          $collection = Mage::getModel('CrmTicket/EmailRouterRules')
                ->getCollection()
                ->addFieldToFilter('cerr_body_pattern', array("neq" => ''))
                ->addFieldToFilter('cerr_active', MDN_CrmTicket_Model_EmailRouterRules::ACTIVE_RULE);

          foreach($collection as $rule) {

              $pattern = Mage::helper('CrmTicket/String')->partenize($rule->getcerr_body_pattern());

              //echo "<br>pattern to check :".$pattern;

              if (preg_match($pattern, $body))
              {
                  $rulesId[] = $rule->getId();
              }
          }
        }

        return $rulesId;
    }

    private function getActivesRulesBySubjectPattern($subject){

        $rulesId = array();

        $subject = Mage::helper('CrmTicket/String')->normalize($subject);

        if($subject){
          $collection = Mage::getModel('CrmTicket/EmailRouterRules')
                ->getCollection()
                ->addFieldToFilter('cerr_subject_pattern', array("neq" => ''))
                ->addFieldToFilter('cerr_active', MDN_CrmTicket_Model_EmailRouterRules::ACTIVE_RULE);

          foreach($collection as $rule) {

              $pattern = Mage::helper('CrmTicket/String')->partenize($rule->getcerr_subject_pattern());

              //echo "<br>pattern to check :".$pattern;

              if (preg_match($pattern, $subject))
              {
                  $rulesId[] = $rule->getId();
              }
          }
        }

        return $rulesId;
    }

    private function getActivesRulesByFromPattern($fromEmail){

        $rulesId = array();

        //echo "<br>from email to test $fromEmail";

        //1st we check email account
        if($fromEmail){
          $collection = Mage::getModel('CrmTicket/EmailRouterRules')
                ->getCollection()
                ->addFieldToFilter('cerr_from_pattern', array("neq" => ''))
                ->addFieldToFilter('cerr_active', MDN_CrmTicket_Model_EmailRouterRules::ACTIVE_RULE);

            foreach($collection as $rule) {

              $pattern = Mage::helper('CrmTicket/String')->partenize($rule->getcerr_from_pattern());

              if (preg_match($pattern, $fromEmail))
              {
                  $rulesId[] = $rule->getId();
              }
            }
        }

        return $rulesId;
    }



    private function getActivesRulesByEmailAccount($emailAccount){

        $rulesId = array();

        //1st we check email account
        if($emailAccount){
          $emailAccount = trim($emailAccount);
          $emailAccountId = Mage::getModel('CrmTicket/EmailAccount')->getEmailAccountByLogin($emailAccount);

          $collection = Mage::getModel('CrmTicket/EmailRouterRules')
                ->getCollection()
                ->addFieldToFilter('cerr_email_account_id', $emailAccountId)
                ->addFieldToFilter('cerr_active', MDN_CrmTicket_Model_EmailRouterRules::ACTIVE_RULE);

            foreach($collection as $rule){
              $rulesId[] = $rule->getId();
            }
        }

        return $rulesId;
    }



}