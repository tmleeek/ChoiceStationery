<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_CustomerPrices
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtex.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Customer Price extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerPrices
 * @author     Webtex Dev Team <dev@webtex.com>
 */

require_once "Mage/Adminhtml/controllers/Catalog/ProductController.php";

class Webtex_CustomerPrices_Adminhtml_Customerprices_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController
{
    public function customerpricesAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function customersgridAction()
    {
        $id = (int) $this->getRequest()->getParam('id', false);
        if($id){
            $product = Mage::getModel('catalog/product')->load($id);
            Mage::register('current_product', $product);        
        }
        
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('customerprices/Adminhtml_Catalog_Product_Tab_CustomerPrices_CustomerGrid_Grid')->toHtml());
    }

    public function customerpricesgridAction()
    {
        $id = (int) $this->getRequest()->getParam('id', false);
        if($id){
            $product = Mage::getModel('catalog/product')->load($id);
            Mage::register('current_product', $product);        
        }
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('customerprices/Adminhtml_Catalog_Product_Tab_CustomerPrices_CustomerPrices_Grid')->toHtml());
    }

    public function deleterowAction()
    {
        $id = (int) $this->getRequest()->getParam('id', false);
        $result = array();

        if($id) {
            $model = Mage::getModel('customerprices/prices')->load($id);

            $product = Mage::getModel('catalog/product')->load($model->getProductId());
            Mage::register('current_product', $product);

            try {
                $model->delete();
                $result['success'] = true;
                $result['messages'] = '<ul class="messages"><li class="success-msg"><ul><li>' . $this->__('Prices was succesfully deleted') . '</li></ul></li></ul>';
                $result['html'] = $this->getLayout()->createBlock('customerprices/Adminhtml_Catalog_Product_Tab_CustomerPrices_CustomerPrices')->toHtml();
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
            $customer = Mage::getModel('customer/customer')->load($model->getCustomerId());
            $result['customer_name'] = $customer->getFirstname() .' '.$customer->getLastname();
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
            
            $product = Mage::getModel('catalog/product')->load($data['product_id']);
            Mage::register('current_product', $product);
            
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
                $result['html'] = $this->getLayout()->createBlock('customerprices/Adminhtml_Catalog_Product_Tab_CustomerPrices_CustomerPrices')->toHtml();
            } catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
        }
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

}
