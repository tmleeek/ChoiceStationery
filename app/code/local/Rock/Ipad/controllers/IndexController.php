<?php
class Rock_Ipad_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() { 
	   $this->loadLayout();
	   $this->getLayout()->getBlock("head")->setTitle($this->__("Ipad"));
	        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Home Page"),
                "title" => $this->__("Home Page"),
                "link"  => Mage::getBaseUrl()
		   ));

      $breadcrumbs->addCrumb("ipad", array(
                "label" => $this->__("Ipad"),
                "title" => $this->__("Ipad")
		   ));

      $this->renderLayout(); 	  
    }

    public function saveAction() {
      $data=$this->getRequest()->getPost();
      if(isset($data['ipadSubmit'])){
        $currentDate = Mage::getModel('core/date')->date();
        $model = Mage::getModel('ipad/ipad');

        $modelData['name'] = $data['ipadName'];
        $modelData['email'] = $data['ipadEmail'];
        $modelData['status'] = 1;
        $modelData['created_on'] = $currentDate;
        $modelData['modified_on'] = $currentDate;

        if(!empty($modelData['name']) && !empty($modelData['email'])){

          if (!Zend_Validate::is($modelData['email'], 'EmailAddress')) {
            Mage::getSingleton('core/session')->addError( Mage::helper('ipad')->__('invalid email address.'));
            $this->_redirectUrl($data['ipadUrl']);
          }
          else{
            $collection=$model->getCollection()
              ->addFieldToSelect('id')
              ->addFieldToFilter('email',$modelData['email']);

            if($collection->getSize()>0){
              Mage::getSingleton('core/session')->addError( Mage::helper('ipad')->__('Email already exists try again.'));
              $this->_redirectUrl($data['ipadUrl']);
            }
            else{
              $model->setData($modelData);

              try{
                $model->save();
				
				//send mail
				$emailTemplate  = Mage::getModel('core/email_template');

				$emailTemplate->loadDefault('rock_ipad_draw');
				$emailTemplate->setTemplateSubject("Thank you, you're in the draw");

				// Get General email address (Admin->Configuration->General->Store Email Addresses)
				$ipadData['email'] = Mage::getStoreConfig('trans_email/ident_general/email');
				$ipadData['name'] = Mage::getStoreConfig('trans_email/ident_general/name');

				$emailTemplate->setSenderName($ipadData['name']);
				$emailTemplate->setSenderEmail($ipadData['email']);

				$emailTemplateVariables['username']  = $data['ipadName'];
				$emailTemplateVariables['storename']=Mage::app()->getStore()->getName();
				$emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
				
				$emailTemplate->send($data['ipadEmail'], Mage::app()->getStore()->getName(), $emailTemplateVariables);
				//send mail
				
                //Mage::getSingleton('core/session')->addSuccess(Mage::helper('ipad')->__('Your request submited successfully.'));
                $this->_redirectUrl(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)."entry_confirmation");
                //  Mage::getSingleton('core/session')->setIpadDrawSuccess(1);
                return;
              }
              catch(Exception $ex){
                Mage::getSingleton('core/session')->addError( Mage::helper('ipad')->__('Not able to submit your request.'));
              }
            }
          }
        }
        else{
          Mage::getSingleton('core/session')->addError( Mage::helper('ipad')->__('Please fill the data properly.'));
          $this->_redirectUrl($data['ipadUrl']);
        }
      }
      
      $this->_redirectUrl($data['ipadUrl']);
    }

    public function SuccessAction() { 
     $this->loadLayout();
     $this->getLayout()->getBlock("head")->setTitle($this->__("Ipad Success"));
          $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Home Page"),
                "title" => $this->__("Home Page"),
                "link"  => Mage::getBaseUrl()
       ));

      $breadcrumbs->addCrumb("ipad", array(
                "label" => $this->__("Ipad Success"),
                "title" => $this->__("Ipad Success")
       ));

      $this->renderLayout();
    }
}