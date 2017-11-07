<?php
class LucidPath_SalesRepPro_Block_Onepage_SalesRep extends Mage_Checkout_Block_Onepage_Abstract {

  protected function _construct() {
    $this->getCheckout()->setStepData('salesrep', array(
      'label'   => Mage::helper('checkout')->__(Mage::getStoreConfig('salesrep/step_setup/step_header')),
      'is_show' => true
    ));

    parent::_construct();
  }
}
?>
