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
 * @copyright  Copyright (c) 2012 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Guillaume SARRAZIN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_CrmTicket_Model_Email_EmailToTicket_StoreDefiner extends Mage_Core_Model_Abstract {
       

    /**
     * Return store id based on email account store ID defined
     * @param type $email
     */
    public function getStore($emailObject)
    {
        $defaultstoreId  = 1;
        
        $emailAccount = $emailObject->getctm_account();
        if($emailAccount){
          $emailAccountObject = Mage::getModel('CrmTicket/EmailAccount')->getRealAccountLogin($emailAccount);
          if($emailAccountObject){
              $defaultstoreId = $emailAccountObject->getStoreId();
          }
        }

        return $defaultstoreId;
    }
    
}
    