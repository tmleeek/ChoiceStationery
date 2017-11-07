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
class MDN_CrmTicket_Model_Email_EmailToTicket_Parser_Default extends MDN_CrmTicket_Model_Email_EmailToTicket_Parser_Abstract {
    
    /**
     * 
     * @param type $email
     */
    public function parse($email, $storeId)
    {
        $values = array('category_id' => null, 'customer' => $this->getCustomer($email, $storeId), 'status' => $this->getDefaultStatus());
        
        return $values;
    }
    
}
    