<?php

class Webtex_CustomerPrices_Adminhtml_Customerprices_CustomerController extends Mage_Adminhtml_Controller_Action
{
    protected function _initCustomer($idFieldName = 'id')
    {
        $this->_title($this->__('Customers'))->_title($this->__('Manage Customers'));

        $customerId = (int) $this->getRequest()->getParam($idFieldName);
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        return $this;
    }

    public function customerpricesAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('customerprices/adminhtml_customer_edit_tab_customerprices')->toHtml());
    }

    public function customerpricesgridAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('customerprices/adminhtml_customer_edit_tab_customerprices_customerprices_grid')->toHtml());
    }

    public function productsgridAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('customerprices/adminhtml_customer_edit_tab_customerprices_productgrid_grid')->toHtml());
    }
    
    public function deleterowAction()
    {
        $id = (int) $this->getRequest()->getParam('id', false);
        $result = array();

        if($id) {
            $model = Mage::getModel('customerprices/prices')->load($id);
            $customer = Mage::getModel('customer/customer')->load($model->getCustomerId());

            Mage::register('current_customer', $customer);

            try {
                $model->delete();
                $result['success'] = true;
                $result['messages'] = '<ul class="messages"><li class="success-msg"><ul><li>' . $this->__('Prices was succesfully deleted') . '</li></ul></li></ul>';
                $result['html'] = $this->getLayout()->createBlock('customerprices/Adminhtml_Customer_Edit_Tab_Customerprices_Customerprices')->toHtml();
            } catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function getproductrowAction()
    {
        $id = (int) $this->getRequest()->getParam('id', false);
        $result = array();

        if($id) {
            $model = Mage::getModel('customerprices/prices')->load($id);
            $result = $model->getData();
            $product = Mage::getModel('catalog/product')->load($model->getProductId());
            $result['product_name'] = $product->getName();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }


    public function addproductrowAction()
    {
        $data = $this->getRequest()->getPost();
        $result = array();
        if(!isset($data['website_id'])){
            $data['website_id'] = 0;
        }

        if(isset($data['product_id']) && isset($data['customer_id'])) {
            $this->_initCustomer('customer_id');
            $customer = Mage::getModel('customer/customer')->load($data['customer_id']);
            $model = Mage::getModel('customerprices/prices')->loadByCustomer($data['product_id'], $data['customer_id'], $data['product_qty'], $data['website_id']);


            $saveData = array(
                'product_id'  => $data['product_id'],
                'customer_id' => $data['customer_id'],
                'customer_email' => $customer->getData('email'),
                'qty'            => $data['product_qty'],
                'store_id'       => $data['website_id'],
                'price'          => $data['product_price'],
                'special_price'  => $data['product_special_price'],
            );
            
            if($model->getId()){
                $saveData['entity_id'] = $model->getId(); 
            }

            $model->setData($saveData);

            try {
                $model->save();
                $result['messages'] = '<ul class="messages"><li class="success-msg"><ul><li>' . $this->__('Prices was succesfully saved') . '</li></ul></li></ul>';
                $result['html'] = $this->getLayout()->createBlock('customerprices/Adminhtml_Customer_Edit_Tab_Customerprices_Customerprices')->toHtml();
            } catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
        }
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    
    protected function _isAllowed()
    {
        return true;
    }

}
