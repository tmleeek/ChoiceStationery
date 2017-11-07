<?php
/**
* @copyright Amasty.
*/ 
class Amasty_List_Model_Observer extends Mage_Core_Model_Abstract
{
    public function processCartAddProductComplete($observer)
    {
        $request = $observer->getRequest();
        $messages = Mage::getSingleton('checkout/session')->getAmlistPendingMessages();
        $urls = Mage::getSingleton('checkout/session')->getAmlistPendingUrls();
        if ($request->getParam('list_next') && count($urls)) {
            $url = array_shift($urls);
            $message = array_shift($messages);

            Mage::getSingleton('checkout/session')->setAmlistPendingUrls($urls);
            Mage::getSingleton('checkout/session')->setAmlistPendingMessages($messages);

            Mage::getSingleton('checkout/session')->addNotice($message);
            
            $observer->getResponse()->setRedirect($url);
            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);
        }
    }     
    
}