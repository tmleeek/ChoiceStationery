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
class MDN_CrmTicket_Model_Customer_Object_Abstract extends Mage_Core_Model_Abstract {

    //Separator for ID of ticket object in DB : like ORDER_10000235
    const ID_SEPARATOR = '_';
    
    public function getObjectType()
    {
        throw new Exception('getObjectType must be implemented !');
    }
    
    public function getObjectName()
    {
        throw new Exception('getObjectName must be implemented !');
    }
        
    public function getObjectAdminLink($objectId)
    {
        throw new Exception('getObjectAdminLink must be implemented !');
    }
    
    public function getObjects($customerId)
    {
        throw new Exception('getObjects must be implemented !');
    }
    
    public function loadObject($id)
    {
        throw new Exception('loadObject must be implemented !');
    }
    
    public function getObjectTitle($id)
    {
        throw new Exception('getObjectTitle must be implemented !');
    }

    public static function getQuickActions()
    {
        throw new Exception('getQuickActions must be implemented !');
    }
    
}