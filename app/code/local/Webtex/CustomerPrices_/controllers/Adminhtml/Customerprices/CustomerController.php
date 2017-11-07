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
    
    public function saveqtyAction()
    {
        $id = (int) $this->getRequest()->getParam('id', false);
        $value = $this->getRequest()->getParam('value', 0);
        if($id) {
            $model = Mage::getModel('customerprices/prices')->load($id);
            $model->setData('qty', $value);
            try {
                $model->save();
                $this->getResponse()->setBody('{"success":true}');
            } catch (Exception $e) {
                $this->getResponse()->setBody('{"error":true}');
            }
        }
    }

    public function savepriceAction()
    {
        $id = (int) $this->getRequest()->getParam('id', false);
        $value = $this->getRequest()->getParam('value', 0);
        if($id) {
            $model = Mage::getModel('customerprices/prices')->load($id);
            $model->setData('price', $value);
            try {
                $model->save();
                $this->getResponse()->setBody('{"success":true}');
            } catch (Exception $e) {
                $this->getResponse()->setBody('{"error":true}');
            }
        }
    }

    public function savespecialpriceAction()
    {
        $id = (int) $this->getRequest()->getParam('id', false);
        $value = $this->getRequest()->getParam('value', 0);
        if($id) {
            $model = Mage::getModel('customerprices/prices')->load($id);
            $model->setData('special_price', $value);
            try {
                $model->save();
                $this->getResponse()->setBody('{"success":true}');
            } catch (Exception $e) {
                $this->getResponse()->setBody('{"error":true}');
            }
        }
    }

    public function deleterowAction()
    {
        $id = (int) $this->getRequest()->getParam('id', false);
        if($id) {
            $model = Mage::getModel('customerprices/prices')->load($id);
            try {
                $model->delete();
                $this->getResponse()->setBody('{"success":true}');
            } catch (Exception $e) {
                $this->getResponse()->setBody('{"error":true}');
            }
        }
    }

    public function addproductrowAction()
    {
        $customer_id = (int) $this->getRequest()->getParam('id', false);
        $product_id = trim($this->getRequest()->getParam('value', 0));
        $text = $customer_id . ' ' . $product_id;
        if($product_id && $customer_id) {
            $product = Mage::getModel('catalog/product')->load($product_id);
            $customer = Mage::getModel('customer/customer')->load($customer_id);;
            
            $model = Mage::getModel('customerprices/prices')->loadByCustomer($product_id, $customer_id, 1);
            $data = array(
                'product_id'  => $product_id,
                'customer_id' => $customer_id,
                'customer_email' => $customer->getData('email'),
                'qty'            => 1,
            );

            $model->setData($data);
            try {
                $model->save();
                $this->getResponse()->setBody($model->getData());
                //$this->getResponse()->setBody('{"success":true}');
            } catch (Exception $e) {
                $this->getResponse()->setBody('{"error":true}');
            }
        }
    }
    
    
    protected function _isAllowed()
    {
        return true;
    }

}
