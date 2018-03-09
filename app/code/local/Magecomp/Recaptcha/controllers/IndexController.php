<?php
class Magecomp_Recaptcha_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
	public function saveAction()
	{
		$nam =$this->getRequest()->getPost('name');
		$emails =$this->getRequest()->getPost('email');
		$telephone =$this->getRequest()->getPost('telephone');
		$cmt =$this->getRequest()->getPost('comment');	
		$receiveEmail = Mage::getStoreConfig('trans_email/ident_general/email'); 
		if($receiveEmail!='')
		{
			try
			{
				$contact = Mage::getModel('recaptcha/recaptcha')
							->setCname($nam)
							->setCemail($emails)
							->setCmobno($telephone)
							->setCcomment($cmt)
							->save();
				Mage::helper('recaptcha')->contactmailsent($receiveEmail,$nam,$emails,$telephone,$cmt);
				Mage::getSingleton('core/session')->addSuccess('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'); 
				return $this->_redirectReferer();
			}
			catch(Exception $e)
			{
				echo $e->getMessage();  
			}
		}
		else
		{
			echo Mage::getSingleton('core/session')->addError(' Please Configure Admin Email.');
			return $this->_redirectReferer();
		}
		$this->loadLayout();     
		$this->renderLayout();
	}
}

?>
