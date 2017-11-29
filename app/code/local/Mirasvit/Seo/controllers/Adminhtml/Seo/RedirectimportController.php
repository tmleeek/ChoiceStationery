<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_seo
 * @version   1.3.18
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


class Mirasvit_Seo_Adminhtml_Seo_RedirectimportController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var Mirasvit_Seo_Helper_Redirect
     */
    protected $_redirectHelper;

    public function _construct()
    {
        $this->_redirectHelper = Mage::helper('seo/redirect');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('seo');
    }

    protected function _initAction ()
    {
        $this->loadLayout()->_setActiveMenu('seo');

        return $this;
    }

    public function indexAction ()
    {
        $this->_title($this->__('Import Urls for Redirect'));
        $this->_initAction();
        $this->_addContent($this->getLayout()
            ->createBlock('seo/adminhtml_redirectimport_edit'));
        $this->renderLayout();
    }


   public function saveAction()
   {
        $data = $this->getRequest()->getParams();

        $uploader = new Mage_Core_Model_File_Uploader('file');
        $uploader->setAllowedExtensions(array('csv'));
        $uploader->setAllowRenameFiles(true);
        $path = Mage::getBaseDir('var').DS.'import';
        if (!file_exists($path)) {
            mkdir($path, 0777);
        }

        try {
            $result = $uploader->save($path);
            $fullPath = $result['path'].DS.$result['file'];

            $csv = new Varien_File_Csv();
            $data = $csv->getData($fullPath);

            $items = array();
            if (count($data) > 1) {
                for ($i = 1; $i < count($data); $i++ ) {
                    $item = array();
                    for ($j = 0; $j < count($data[0]); $j++) {
                        if (isset($data[$i][$j]) && trim($data[$i][$j]) != '') {
                            $item[strtolower($data[0][$j])] = $data[$i][$j];
                        }
                    }
                    $items[] = $item;
                }
            }

            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $table = $resource->getTableName('seo/redirect');
            $table2 = $resource->getTableName('seo/redirect_store');
            $i = 0;
            $notImported = 0;
            foreach ($items as $item) {
                if (!isset($item['url_from']) || !isset($item['url_to'])) {
                    continue;
                }

                if ($this->_redirectHelper->_checkRedirectPattern($item['url_from'], $item['url_to'])
                    && $item['is_redirect_only_error_page'] != 1) {
                    $notImported++;
                    continue;
                }

                $item = new Varien_Object($item);
                $queryRedirects = "REPLACE {$table} SET
                    url_from = '".addslashes($item->getUrlFrom())."',
                    url_to = '".addslashes($item->getUrlTo())."',
                    is_redirect_only_error_page = '".(int)($item->getIsRedirectOnlyErrorPage())."',
                    redirect_type = '".(int)($item->getRedirectType())."',
                    sort_order = '".(int)($item->getSortOrder() ? $item->getSortOrder() : 10)."',
                    comments = '".addslashes($item->getComments())."',
                    is_active = '".(int)($item->getIsActive())."';
                ";
                $writeConnection->query($queryRedirects);
                $redirectId = $writeConnection->lastInsertId();

                $storeIds = array();
                $storeIds = explode(',', $item->getStoreId());
                foreach ($storeIds as $storeId) {
                    $queryRedirectsToStore = "REPLACE {$table2} SET
                        store_id = '".(int)($storeId)."',
                        redirect_id = '".(int)($redirectId)."';
                    ";

                    $writeConnection->query($queryRedirectsToStore);
                }

                $i ++;
            }

            Mage::getSingleton('adminhtml/session')->addSuccess(''.$i.' records were inserted');
            if ($notImported) {
                Mage::getSingleton('adminhtml/session')->addWarning(''.$notImported.' records were not inserted as they might cause redirect loop');
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}