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
class MDN_CrmTicket_Admin_CategoryController extends Mage_Adminhtml_Controller_Action {

    public function GridAction() {
        $this->loadLayout();
        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('CRM categories'));
        $this->renderLayout();
   }

   public function TreeAction() {
        $this->loadLayout();
        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('CRM categories'));
        $this->renderLayout();
   }

    public function EditAction() {

        $this->loadLayout();
        $categoryId = $this->getRequest()->getParam('category_id');
        Mage::register('ctc_id', $categoryId);
        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Edit CRM category N.'.$categoryId));
        $this->renderLayout();
    }
    
    /**
     * version for Tree edition
     */
    public function EditCategoryAction() {
       
        $categoryId = $this->getRequest()->getParam('category_id');
        Mage::register('ctc_id', $categoryId); 
               
        $html = $this->getLayout()->createBlock('CrmTicket/Admin_Category_Edit')->setTemplate('CrmTicket/Category/Edit/Tab/Category.phtml')->toHtml();

        $this->getResponse()->setBody($html);
    }

    /**
     * version for Tree edition
     */
    public function AddCategoryAction() {

        $html = $this->getLayout()->createBlock('CrmTicket/Admin_Category_New')->setTemplate('CrmTicket/Category/New.phtml')->toHtml();

        $this->getResponse()->setBody($html);
    }

    /**
     *
     */
    public function SaveAction() {

        // get category id
        $categoryId = $this->getRequest()->getPost('ctc_id');

        $data = $this->getRequest()->getPost();
        unset($data['ctc_id']);

        if ($data['ctc_reply_delay'] == '')
            $data['ctc_reply_delay'] = new Zend_Db_Expr('null');
        
        // load category
        $category = mage::getModel('CrmTicket/Category')->load($categoryId);
        
        foreach ($data as $key => $value) {          
            $category->setData($key, $value);
        }
        $category->save();
        

        //confirm
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));

        $this->_redirect('CrmTicket/Admin_Category/Tree', array('category_id' => $categoryId));
    }

    /**
     * Delete a category and potentially his sub categories
     */
    public function DeleteAction() {

        $categoryId = $this->getRequest()->getParam('category_id');

        $category = mage::getModel('CrmTicket/Category')->load($categoryId);

        //first delete subcategories
        $subCategories = $category->getOwnSubCategories();
        foreach ($subCategories as $subCategory){
          $subCategory->delete();
        }

        //then delete selected category
        $category->delete();

        //confirm
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Category deleted'));

        //Redirect        
        $this->_redirect('CrmTicket/Admin_Category/Tree');
    }

    /**
     * link to the form of the new category 
     */
    public function NewCategoryAction() {
        $this->loadLayout();
        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('New category'));
        $this->renderLayout();
    }

    /**
     * save new category 
     */
    public function CreateNewCategoryAction() {
   
        // get data
        $data = $this->getRequest()->getPost('category');

        // load category
        $category = mage::getModel('CrmTicket/Category');
        $category->setctc_name($data["ctc_name"]);
        $category->setctc_parent_id($data["ctc_parent_id"]);
        //$category->setctc_category_type($data["ctc_category_type"]);
        if (Mage::helper('CrmTicket')->allowProductSelection()){
          $category->setctc_produit_id($data["ctc_produit_id"]);
        }
        $category->setctc_manager($data["ctc_manager"]);
        $category->setctc_is_private($data["ctc_is_private"]);
        if ($data['ctc_reply_delay'] == '')
            $data['ctc_reply_delay'] = new Zend_Db_Expr('null');
        $category->setctc_reply_delay($data["ctc_reply_delay"]);
        $category->save();

        //confirm
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Category created'));

        //Redirect
        $this->_redirect('CrmTicket/Admin_Category/Tree');
    }
    
    /**
     * Test connection to SMTP
     */
    public function TestConnectionAction()
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        try
        {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Connection successfull'));
        }
        catch(Exception $ex)
        {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('An error occured : %s', $ex->getMessage()));
        }
        
        //Redirect
        $this->_redirect('CrmTicket/Admin_Category/Edit', array('category_id' => $categoryId));
    }
	
    protected function _isAllowed() {
        return true;
    }
}