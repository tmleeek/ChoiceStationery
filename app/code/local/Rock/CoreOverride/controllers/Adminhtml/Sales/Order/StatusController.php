<?php
require_once "Mage/Adminhtml/controllers/Sales/Order/StatusController.php";  
class Rock_CoreOverride_Adminhtml_Sales_Order_StatusController extends Mage_Adminhtml_Sales_Order_StatusController{

   /**
     * Unassign the status from a specific state
     */
    public function unassignAction()
    {
        $state  = $this->getRequest()->getParam('state');
        $status = $this->_initStatus();
        if ($status) {
            try {
               /* Mage::dispatchEvent('sales_order_status_unassign_before', array(
                    'status' => $status, // string {new,     ...}
                    'state'  => $state   // Model  {Pending, ...}
                ));*/
                $status->unassignState($state);
                $this->_getSession()->addSuccess(Mage::helper('sales')->__('The order status has been unassigned.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException(
                    $e,
                    Mage::helper('sales')->__('An error occurred while unassigning order status.')
                );
            }
        } else {
            $this->_getSession()->addError(Mage::helper('sales')->__('Order status does not exist.'));
        }
        $this->_redirect('*/*/');
    }


}
				