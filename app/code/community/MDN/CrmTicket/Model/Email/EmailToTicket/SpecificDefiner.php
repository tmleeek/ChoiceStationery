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
class MDN_CrmTicket_Model_Email_EmailToTicket_SpecificDefiner extends Mage_Core_Model_Abstract {
    
    /**
     * Route to the adapted parser
     *
     * @param type $email
     */
    public function parse($email, $storeId)
    {
        $domain = Mage::helper('CrmTicket/String')->getDomainFromEmail($email->fromEmail);
        
        //load parser based on domain
        $parser = null;
        switch($domain)
        {            
            default:
                $parser = Mage::getModel('CrmTicket/Email_EmailToTicket_Parser_Default');
                break;
        }
        
        //apply parser
        $values =null;
        if($parser){
          $values = $parser->parse($email, $storeId);
        }
        
        return $values;
    }
   
}
    