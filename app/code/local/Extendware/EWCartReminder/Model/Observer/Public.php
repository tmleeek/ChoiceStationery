<?php

class Extendware_EWCartReminder_Model_Observer_Public
{
	static public function beforeSend($observer){
        $reminder = $observer->getEvent()->getReminder();
        $isAuto = $observer->getEvent()->getIsAuto();
        $returnObject = $observer->getEvent()->getReturnObject();
		if ($isAuto === false) return;
		
        $isInventoryRequired = (bool)$reminder->getReminderConfigData('conditions/inventory_required');
        $backupAction = $reminder->getReminderConfigData('conditions/backup_action');
        $backupActionDelay = Mage::helper('ewcartreminder')->getSecondsByData($reminder->getReminderConfigData('conditions/delay_magnitude'), $reminder->getReminderConfigData('conditions/delay_period'));
        if ($isInventoryRequired === false) return;
        	
        $productIds = $reminder->getProductIds(); 
        if (empty($productIds)) return;
        
        $canSend = false;
        foreach ($productIds as $productId) {
        	$product = Mage::getModel('catalog/product')->load($productId);
        	if ($product->getIsInStock() === true) {
        		$canSend = true;
        	}
        }

        if ($canSend === true) {
        	$returnObject->setAction('send');
        	return;
        }
        
		if ((time() - strtotime($reminder->getScheduledAt())) >= $backupActionDelay) {
			$returnObject->setAction($backupAction);
		}
    }
}
