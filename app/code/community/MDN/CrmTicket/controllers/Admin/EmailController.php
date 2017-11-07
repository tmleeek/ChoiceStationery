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
class MDN_CrmTicket_Admin_EmailController extends Mage_Adminhtml_Controller_Action {

    public function GridAction() {
        $this->loadLayout();

        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('CRM Saved Emails'));

        $this->renderLayout();
    }

    public function EditAction() {

        //bulk display parsed

        try
        {
          $emailId = $this->getRequest()->getParam('ctm_id');
          $debugMode = $this->getRequest()->getParam('dbg');
          $email = Mage::getModel('CrmTicket/Email')->load($emailId);

          $result = $email->extractInfosFromRawMail();

            if ($result) {

                echo '<p><b>From :</b>' . $result->fromEmail . ' : ' . $result->fromName . '</p>';
                echo '<p><b>To : </b>' . $result->to . '</p>';
                echo '<p><b>Subject :</b> ' . $result->subject . '</p>';
                if($debugMode){
                  echo '<p>Subject Normalized : ' . Mage::helper('CrmTicket/String')->normalize($result->subject) . '</p>';
                }
                echo '<p><b>Body :</b></p>';
                echo $result->response;

                if($debugMode){
                  echo '<br/><p>Final Response Inserred : </p>';
                  $etotModel = Mage::getModel('CrmTicket/Email_EmailToTicket');
                  $response = $etotModel->removeResponsesFromPreviousTicket($result->response);
                  //$response = $etotModel->removeUselessText($result, $response);
                  $response = trim(strip_tags($response, '<p><br>'));
                  $response = $etotModel->consolidateUnclosedTags($response);
                  echo $response;


                  echo '<br/>vardump : <pre>';
                  var_dump($result);
                  echo '</pre>';

                  //try to link
                  $ticket = Mage::getModel('CrmTicket/Email_EmailToTicket_TicketDefiner')->getTicket($result);
                  
                  if($ticket){

                    echo '<br/>Linked to ticket :<pre>';
                    var_dump($ticket);
                    echo '</pre>';
                  }
                  /*else{
                      //set store
                      //$storeId = Mage::getModel('CrmTicket/Email_EmailToTicket_StoreDefiner')->getStore($result);

                      //parse datas
                      //$specificDatas = Mage::getModel('CrmTicket/Email_EmailToTicket_SpecificDefiner')->parse($result, $storeId);

                      echo '<br/>NOT linked to ticket : Store id '.$storeId.'<pre>';
                      var_dump($specificDatas);
                      echo '</pre>';
                  }*/
                }

            } else {
                echo 'Unable to extract email !';
            }

        }
        catch(Exception $ex){
          echo '<br/>Exception :<pre>'.$ex.'</pre>';
        }
    }

    /**
     * 
     */
    public function ConvertNewMailAction() {
        try
        {
            $count = Mage::getModel('CrmTicket/Email')->processNewEmails();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s emails converted to ticket', $count));
        }
        catch(Exception $ex)
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('%s', $ex->getMessage()));
        }        
        $this->_redirect('CrmTicket/Admin_Email/Grid');
    }
    
    /**
     * Mass action : associate mail to ticket
     */
    public function MassAssociateToTicketAction()
    {
        $ids = $this->getRequest()->getPost('ctm_ids');

        foreach($ids as $id)
        {
            $mail = Mage::getModel('CrmTicket/Email')->load($id);
            $ignoreSpam = true;
            $mail->convertToTicket($ignoreSpam);
        }        

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Emails processed'));
        $this->_redirect('*/*/Grid');
    }
	
    protected function _isAllowed() {
        return true;
    }
}