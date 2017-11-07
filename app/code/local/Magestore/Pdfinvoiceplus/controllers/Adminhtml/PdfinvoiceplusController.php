<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Pdfinvoiceplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Pdfinvoiceplus Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Pdfinvoiceplus
 * @author      Magestore Developer
 */
class Magestore_Pdfinvoiceplus_Adminhtml_PdfinvoiceplusController extends Mage_Adminhtml_Controller_Action {

    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Pdfinvoiceplus_Adminhtml_PdfinvoiceplusController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('pdfinvoiceplus/pdfinvoiceplus')
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager')
        );
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
        $this->initTemplates(false);
        $this->_initAction()
                ->renderLayout();
    }

    /**
     * view and edit item action
     */
    public function editAction() {
        $templateId = $this->getRequest()->getParam('id');
        $model = Mage::getModel('pdfinvoiceplus/template')->load($templateId);

        if ($model->getId() || $templateId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('pdfinvoiceplus_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('pdfinvoiceplus/pdfinvoiceplus');

            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Template Manager'), Mage::helper('adminhtml')->__('Template Manager')
            );
            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Template News'), Mage::helper('adminhtml')->__('Template News')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('pdfinvoiceplus/adminhtml_pdfinvoiceplus_edit'))
                    ->_addLeft($this->getLayout()->createBlock('pdfinvoiceplus/adminhtml_pdfinvoiceplus_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('pdfinvoiceplus')->__('Template does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    /**
     * save item action
     */
    public function saveAction() {
        if (isset($_FILES['template_upload']['name']) && $_FILES['template_upload']['name'] != '') {
            try {
                /* Starting upload */
                $uploader = new Varien_File_Uploader('template_upload');

                // Any extention would work
                $uploader->setAllowedExtensions(array('zip'));
                $uploader->setAllowRenameFiles(false);

                // Set the file upload mode 
                // false -> get the file directly in the specified folder
                // true -> get the file in the product like folders 
                //    (file.jpg will go in something like /media/f/i/file.jpg)
                $uploader->setFilesDispersion(false);

                // We set media as the upload dir
                $path = Mage::getBaseDir('media') . DS . 'magestore' . DS . 'pdfinvoiceplus';
                $result = $uploader->save($path, $_FILES['template_upload']['name']);
                $target_directory= $path;
                try{
                    $fileName = str_replace(array('.zip','.ZIP'), '', $_FILES['template_upload']['name']);
                    $adminhtmlPath = Mage::getBaseDir().DS.'app'.DS.'design'.DS.'adminhtml'.DS.'default'.DS.'default'.DS.'template'.DS.'pdfinvoiceplus'.DS.'templates'.DS.$fileName;
                    $frontendPath = Mage::getBaseDir().DS.'app'.DS.'design'.DS.'frontend'.DS.'default'.DS.'default'.DS.'template'.DS.'pdfinvoiceplus'.DS.'templates'.DS.$fileName;
                    $zip = new ZipArchive;
                    $res = $zip->open($path . DS . $_FILES['template_upload']['name']);
                    if ($res == TRUE) {
                        $zip->extractTo($path, array($fileName.'.jpg', $fileName.'.xml'));
                        $zip->extractTo($path.DS.$fileName, array('frontend.zip','adminhtml.zip'));
                        //Zend_Debug::dump($path.DS.$fileName);die('1');
                        $zip->close();
                        $frontendRes = $zip->open($path.DS.$fileName.DS.'frontend.zip');
                        if($frontendRes)
                            $zip->extractTo($frontendPath, array('order.phtml', 'invoice.phtml', 'creditmemo.phtml'));
                        $zip->close();
                        $adminhtmlRes = $zip->open($path.DS.$fileName.DS.'adminhtml.zip');
                        if($adminhtmlRes)
                            $zip->extractTo($adminhtmlPath, array('order.phtml', 'invoice.phtml', 'creditmemo.phtml'));
                        $zip->close();
                    } else {
                        Mage::getSingleton('adminhtml/session')->addError(
                            Mage::helper('pdfinvoiceplus')->__(
                                Mage::helper('pdfinvoiceplus')->__('Template uploaded failed!')
                            )
                        );
                    }
                    /*$options = array( 
                        'adapter' => 'zip', 
                        'options' => array( 
                                 'target' => Mage::getBaseDir().DS.'media'.DS.'pdfinvoiceplus', 
                                 ), 
                    ); 
                    $filter = new Zend_Filter_Decompress($options);
                    $compressed = $filter->filter($path . DS . $_FILES['template_upload']['name']);*/
                    $this->initTemplates();
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('pdfinvoiceplus')->__('Template is successfully uploaded!')
                    );
                }catch(Exception $e){
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('pdfinvoiceplus')->__($e->getMessage())
                    );
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('pdfinvoiceplus')->__($e->getMessage())
                );
            }
        }
        /**/
        if ($data = $this->getRequest()->getPost()) {
            if (isset($data['company_logo_delete']) && $data['company_logo_delete']) {
                $data['company_logo'] = null;
            } else {
                if (isset($_FILES['company_logo']['name']) && $_FILES['company_logo']['name'] != '') {
                    try {
                        /* Starting upload */
                        $uploader = new Varien_File_Uploader('company_logo');

                        // Any extention would work
                        $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                        $uploader->setAllowRenameFiles(false);

                        // Set the file upload mode 
                        // false -> get the file directly in the specified folder
                        // true -> get the file in the product like folders 
                        //    (file.jpg will go in something like /media/f/i/file.jpg)
                        $uploader->setFilesDispersion(false);

                        // We set media as the upload dir
                        $path = Mage::getBaseDir('media') . DS . 'magestore' . DS . 'pdfinvoiceplus';
                        $result = $uploader->save($path, $_FILES['company_logo']['name']);
                        $data['company_logo'] = $result['file'];
                    } catch (Exception $e) {
                        $data['company_logo'] = $_FILES['company_logo']['name'];
                    }
                }
            }
            $model = Mage::getModel('pdfinvoiceplus/template')
                    ->load($this->getRequest()->getParam('id'));
            if (isset($data['stores']) && is_array($data['stores'])) {
                $stores = implode(',', $data['stores']);
                $data['stores'] = $stores;
            }else{
                $data['stores'] = 0;
            }
            if(!isset($data['color']))
                $data['color'] = '';
            
            $model->addData($data)
                    ->setId($this->getRequest()->getParam('id'));
            if ($model->getCreatedAt() == NULL) {
                $model->setCreatedAt(now());
            }
            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('pdfinvoiceplus')->__('Template was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('pdfinvoiceplus')->__('Unable to find template to save')
        );
        $this->_redirect('*/*/');
    }
    
    public function initTemplates($check=true){
        $yourStartingPath = Mage::getBaseDir('media') . DS . 'magestore/' . DS . 'pdfinvoiceplus';
        $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($yourStartingPath), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $templateName = $file->getFileName();
                if (strpos($templateName, 'template') === 0 && strpos($templateName, '.xml') > 0) {
                    $templateName = str_replace('.xml', '', $templateName);
                    
                    
                    $collection = Mage::getModel('pdfinvoiceplus/systemtemplate')
                            ->getCollection()
                            ->addFieldToFilter('template_code', $templateName);
                    
                    if ($collection->getSize() == 0) {
                        $xmlPath = $yourStartingPath.DS.$templateName.'.xml';
                        $xmlObj = new Varien_Simplexml_Config($xmlPath);
                        $xmlData = $xmlObj->getNode();
                        if($xmlData){
                            $data = $xmlData->asArray();
                            if(!isset($data['name']) || $data['name'] == ''){
                                if($check){
                                    Mage::getSingleton('adminhtml/session')->addError(
                                            Mage::helper('pdfinvoiceplus')->__('Uploaded template is not valid.')
                                    );
                                }
                                return;
                            }
                            if(!isset($data['secret_key']) || $data['secret_key'] != md5('pdfinvoiceplus_magestore_'.$data['code'])){
                                if($check){
                                    Mage::getSingleton('adminhtml/session')->addError(
                                            Mage::helper('pdfinvoiceplus')->__('Uploaded template is not valid.')
                                    );
                                }
                                return;
                            }
                            $template = Mage::getModel('pdfinvoiceplus/systemtemplate');
                            $template->setTemplateName($data['name']);
                            if(isset($data['code']) && $data['code'] != ''){
                                $template->setTemplateCode($data['code']);
                            }
                            if(isset($data['image']) && $data['image'] != ''){
                                $template->setImage($data['image']);
                            }
                            try{
                                $template->setId(null)
                                    ->save();
                            }  catch (Exception $e){
                                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * delete item action
     */
    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('pdfinvoiceplus/template');
                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Template was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * mass delete item(s) action
     */
    public function massDeleteAction() {
        $templateIds = $this->getRequest()->getParam('pdfinvoiceplus');
        if (!is_array($templateIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select template(s)'));
        } else {
            try {
                foreach ($templateIds as $templateId) {
                    $template = Mage::getModel('pdfinvoiceplus/template')->load($templateId);
                    $template->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($templateIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass change status for item(s) action
     */
    public function massStatusAction() {
        $templateIds = $this->getRequest()->getParam('pdfinvoiceplus');
        if (!is_array($templateIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select template(s)'));
        } else {
            try {
                foreach ($templateIds as $templateId) {
                    Mage::getSingleton('pdfinvoiceplus/template')
                            ->load($templateId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($templateIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        $fileName = 'pdfinvoiceplus.csv';
        $content = $this->getLayout()
                ->createBlock('pdfinvoiceplus/adminhtml_pdfinvoiceplus_grid')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
        $fileName = 'pdfinvoiceplus.xml';
        $content = $this->getLayout()
                ->createBlock('pdfinvoiceplus/adminhtml_pdfinvoiceplus_grid')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('pdfinvoiceplus');
    }

    public function showTemplateAction() {
        $form_html = $this->getLayout()
                ->createBlock('pdfinvoiceplus/adminhtml_pdfinvoiceplus')
                ->setTemplate('pdfinvoiceplus/systemtemplate.phtml')
                ->toHtml();
        $this->getResponse()->setBody($form_html);
    }

}