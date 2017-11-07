<?php
require_once "Mage/Adminhtml/controllers/Sales/OrderController.php";  
class Rock_CoreOverride_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
	 /**
     * View order detale
     */
    public function viewAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Orders'));

        $order = $this->_initOrder();
        if ($order) {

            /*$isActionsNotPermitted = $order->getActionFlag(
                Mage_Sales_Model_Order::ACTION_FLAG_PRODUCTS_PERMISSION_DENIED
            );
            if ($isActionsNotPermitted) {
                $this->_getSession()->addError($this->__('You don\'t have permissions to manage this order because of one or more products are not permitted for your website.'));
            }*/

            $this->_initAction();

            $this->_title(sprintf("#%s", $order->getRealOrderId()));

            $this->renderLayout();
        }
    }
}
				