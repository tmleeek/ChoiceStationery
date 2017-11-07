<?php
require_once 'Mage/Checkout/controllers/OnepageController.php';

class LucidPath_SalesRepPro_OnepageController extends Mage_Checkout_OnepageController {

  public function savePaymentAction() {
    $this->_expireAjax();

    if (Mage::getStoreConfig('salesrep/module_status/enabled') && Mage::getStoreConfig('salesrep/step_setup/step_enabled')) {
      if ($this->getRequest()->isPost()) {
        $data = $this->getRequest()->getPost('payment', array());

        // first to check payment information entered is correct or not
        try {
          $result = $this->getOnepage()->savePayment($data);
        }
        catch (Mage_Payment_Exception $e) {
          if ($e->getFields()) {
            $result['fields'] = $e->getFields();
          }
          $result['error'] = $e->getMessage();
        }
        catch (Exception $e) {
          $result['error'] = $e->getMessage();
        }
        $redirectUrl = $this->getOnePage()->getQuote()->getPayment()->getCheckoutRedirectUrl();

        if (Mage::getStoreConfig('salesrep/module_status/enabled') && Mage::getStoreConfig('salesrep/step_setup/step_enabled')) {
          if (empty($result['error']) && !$redirectUrl) {
            $this->loadLayout('checkout_onepage_salesrep');

            $result['goto_section'] = 'salesrep';

            $this->getOnePage()->getCheckout()->setStepData('payment_method', 'complete', true);
          }
        } else {
          Mage::getSingleton('core/session')->setData('salesrep_rep_id', '');
          $this->loadLayout('checkout_onepage_review');

          $result['goto_section'] = 'review';
          $result['update_section'] = array(
            'name' => 'review',
            'html' => $this->_getReviewHtml()
          );
        }

        if ($redirectUrl) {
          $result['redirect'] = $redirectUrl;
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
      }
    } else {
      parent::savePaymentAction();
    }
  }

  public function saveSalesrepAction() {
    $this->_expireAjax();
    if ($this->getRequest()->isPost()) {
      $result = array();

      try {
        $rep_model = Mage::getModel('admin/user')->load(intval($this->getRequest()->getPost('salesrep_rep')));

        Mage::getSingleton('core/session')->setSalesrepAdminId($rep_model->getId());

        $this->getOnePage()->getCheckout()->setAdminName($rep_model->getFirstname() .' '. $rep_model->getLastname());
        $this->getOnePage()->getCheckout()->setStepData('salesrep', 'complete', true);
      } catch (Exception $e) {
        $result['error'] = $e->getMessage();
      }

      $redirectUrl = $this->getOnePage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
      if (!$redirectUrl) {
        $this->loadLayout('checkout_onepage_review');

        $result['goto_section'] = 'review';
        $result['update_section'] = array(
          'name' => 'review',
          'html' => $this->_getReviewHtml()
        );
      }

      if ($redirectUrl) {
        $result['redirect'] = $redirectUrl;
      }

      $this->getResponse()->setBody(Zend_Json::encode($result));
    }
  }
}
?>
