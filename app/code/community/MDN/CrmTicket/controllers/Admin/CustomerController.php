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
class MDN_CrmTicket_Admin_CustomerController extends Mage_Adminhtml_Controller_Action {

    /**
     * Redirect to customer object view 
     */
    public function ViewObjectAction()
    {
        //get param
        $param = $this->getRequest()->getParam('object_id');
        list($objectType, $objectId) = explode('_', $param);
        
        //find class
        $class = Mage::getModel('CrmTicket/Customer_Object')->getClassByType($objectType);
        if (!$class)
            die('Unable to find class for object '.$objectType);
        
        $urlInfo = $class->getObjectAdminLink($objectId);
        
        $this->_redirect($urlInfo['url'], $urlInfo['param']);
    }
    
    /**
     * Display object summary in popup
     */
    public function ViewObjectPopupAction()
    {

        //create block
        try
        {
            //get param
            $param = $this->getRequest()->getParam('object_id');
            if ($param)
            {
                list($objectType, $objectId) = explode('_', $param);

                //find class
                $class = Mage::getModel('CrmTicket/Customer_Object')->getClassByType($objectType);
                if (!$class)
                    die('Throw new exception '.$objectType);

                $block = $this->getLayout()->createBlock('CrmTicket/Admin_Object_Popup');
                $block->setObjectType($objectType);
                $block->setObjectId($objectId);
                $block->setTemplate('CrmTicket/ObjectPopup/'.$objectType.'.phtml');
                $html = $block->toHtml();
            }
            else
                $html = $this->__('No object selected');
        }
        catch(Exception $ex)
        {
            $html = $ex->getMessage();
        }
        
        echo $html;
        
    }

    /**
     * Create the new Customer
     */
    public function CreateAction()
	{

       $created = false;
       $exist = false;

       $request = $this->getRequest();

       $storeId = $request->getPost('customer_store_id');
       $customerEmail = trim($request->getPost('customer_email'));
       $customerFirstName = trim($request->getPost('customer_first_name'));
       $customerLastName = trim($request->getPost('customer_last_name'));
       $customerPhone = trim($request->getPost('customer_phone'));

       $debug = '';

        try {


          $debug.= "<br> email:$customerEmail fname:$customerFirstName lname:$customerLastName phone:$customerPhone store:$storeId<br>";

          if($customerEmail && $customerFirstName & $customerLastName){
            $customerModel = Mage::getModel('customer/customer');
            $customerModel->setWebsiteId($storeId);

            $store = Mage::getModel('core/store')->load($storeId);
            $websiteId = $store->getwebsite_id();

            $customerModel->loadByEmail($customerEmail);//not working ...
            $customerId = $customerModel->getId();

            $debug.= "<br>Existing customer : cid=$customerId<br>";

            if (!$customerId) {
              $customerModel->setEmail($customerEmail);
              $customerModel->setFirstname($customerFirstName);
              if($customerLastName){
                $customerModel->setLastname($customerLastName);
              }

              $password = substr(base64_encode("your password."), 0, 10); //TO IMPROVE :)
              $customerModel->setPassword($password);
              $customerModel->setWebsiteStoreId($storeId);
              $customerModel->setwebsite_id($websiteId);
              $customerModel->setStoreId($storeId);
              $customerModel->save();


              $customerId = $customerModel->getId();

              //Manage Phone Number
              if($customerPhone){
                $customAddress = array (
                      'firstname' => $customerFirstName,
                      'lastname' => $customerLastName,
                      'telephone' => $customerPhone
                  );
                $customAddressModel = Mage::getModel('customer/address');
                $customAddressModel->setData($customAddress)
                  ->setCustomerId($customerId)
                  ->setIsDefaultBilling('1')
                  ->setIsDefaultShipping('1')
                  ->setSaveInAddressBook('1');
                try {
                    $customAddressModel->save();
                }
                catch (Exception $ex) {
                    //Zend_Debug::dump($ex->getMessage());
                }
              }

              if ($customerId) {
                 $created = true;
                 $debug.= "<br>New created customer : cid=$customerId<br>";
             }

            }else{
              $exist = true;
            }
          }else{
            Mage::getSingleton('adminhtml/session')->addError($this->__('Email adress, first and last name are mandatory'));
          }

         

          if($exist){
            Mage::getSingleton('adminhtml/session')->addError($this->__('Customer #%s allready exist', $customerId));
          }elseif($created){
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Customer #%s created', $customerId));
          }else{
            Mage::getSingleton('adminhtml/session')->addError($this->__('Failed to create customer'));
          }
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : %s', $ex->getMessage()));
        }


        if(!$created){
           //Mage::getSingleton('adminhtml/session')->addError($this->__($debug));
           $this->_redirect('CrmTicket/Admin_Ticket/SearchCreate/Grid');
        }else{
          $this->_redirect('CrmTicket/Admin_Ticket/SearchCreate/Grid', array('customer_id' => $customerId));
        }

    }
    
	protected function _isAllowed() {
        return true;
    }    
}