<?php
/**
 * @category Customer Version 2.5
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 21.01.2015
 */
class MageB2B_Sublogin_Model_Mysql4_Budget extends Mage_Core_Model_Mysql4_Abstract implements Mage_Eav_Model_Entity_Interface
{
    public function _construct()
    {
        $this->_init('sublogin/budget', 'budget_id');
    }
}

