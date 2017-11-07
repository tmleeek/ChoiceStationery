<?php
require_once "Mage/Customer/controllers/AccountController.php";  
class Rock_CoreOverride_Customer_AccountController extends Mage_Customer_AccountController{

    public function postDispatch()
    {
        parent::postDispatch();
        Mage::dispatchEvent('controller_action_postdispatch_adminhtml', array('controller_action' => $this));
    }


}
				