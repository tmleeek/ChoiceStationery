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


class Mirasvit_Seo_Adminhtml_Seo_RedirectController extends Mage_Adminhtml_Controller_Action
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
        $this->_title($this->__('Redirect Manager'));
        $this->_initAction();
        $this->_addContent($this->getLayout()
            ->createBlock('seo/adminhtml_redirect'));
        $this->renderLayout();
    }

    public function addAction ()
    {
        $this->_title($this->__('New Redirect'));

        $this->_initModel();

        $this->_initAction();
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Redirect  Manager'),
                Mage::helper('adminhtml')->__('Redirect Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add Redirect '), Mage::helper('adminhtml')->__('Add Redirect'));

        $this->getLayout()
            ->getBlock('head')
            ->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('seo/adminhtml_redirect_edit'));

        $this->renderLayout();
    }

    public function editAction ()
    {
        $model = $this->_initModel();

        if ($model->getId()) {
            $this->_initAction();
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Redirect Manager'),
                    Mage::helper('adminhtml')->__('Redirect Manager'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Redirect '),
                    Mage::helper('adminhtml')->__('Edit Redirect '));

            $this->getLayout()
                ->getBlock('head')
                ->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('seo/adminhtml_redirect_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('The item does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction ()
    {
        if ($data = $this->getRequest()->getPost()) {
            $data['url_from']        = trim($data['url_from']);
            $data['url_to']          = trim($data['url_to']);
            $data['is_redirect_only_error_page'] = !empty($data['is_redirect_only_error_page']);
            $data = Mage::helper('seo/checkdata')->prepareSaveActionData($data);

            $model = $this->_initModel();
            $model->setData($data);

            try {
                $model->save();
                if ($this->_redirectHelper->_checkRedirectPattern($data['url_from'], $data['url_to'])
                    && $data['is_redirect_only_error_page'] != 1) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Item can not be saved as'
                        .' "Target URL" matches "Request Url" pattern. This will cause redirect loop.'
                        .' Please alter one of those fields.'));
                    Mage::getSingleton('adminhtml/session')->setFormData(false);
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                if ($model->getComments() == 'Example redirect rule'
                    && $model->getIsActive()
                    && $baseUrl = rtrim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB), "/")) {
                        Mage::getSingleton('adminhtml/session')->addNotice('Example redirect rule has been enabled.'
                            .' It redirects all pages that return "404 Not Found" error(like '.$baseUrl.'/unexistant/url.html)'
                            .' to the Homepage of your site('.$baseUrl.').');
                    }
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction ()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('seo/redirect');

                $model->setId($this->getRequest()
                    ->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()
                    ->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('redirect_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('seo/redirect')
                        ->setIsMassDelete(true)
                        ->load($id);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massEnableAction()
    {
        $ids = $this->getRequest()->getParam('redirect_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            $notEnabledCount = 0;
            $notEnabledIds = "";
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('seo/redirect')->load($id);
                    if ($this->_redirectHelper->_checkRedirectPattern($model->getUrlFrom(), $model->getUrlTo())
                        && $model->getIsRedirectOnlyErrorPage() != 1) {
                        $model->setIsActive(false);
                        $notEnabledIds .= $model->getId() . "; ";
                        $model->save();
                        $notEnabledCount++;
                        continue;
                    } else {
                        $model->setIsActive(true);
                    }
                    $storeIds = $model->getStoreIds();
                    if (!$storeIds || Mage::helper('seo/checkdata')->isMassEnableActionDataPrepare($storeIds)) {
                        $model->setStoreIds(0);
                    }
                    $model->save();
                    if ($model->getComments() == 'Example redirect rule'
                        && $baseUrl = rtrim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB), "/")) {
                        Mage::getSingleton('adminhtml/session')->addNotice('Example redirect rule has been enabled.'
                            .' It redirects all pages that return "404 Not Found" error(like '.$baseUrl.'/unexistant/url.html)'
                            .' to the Homepage of your site('.$baseUrl.').');
                    }
                }
                $idCount = count($ids);
                $enabledCount = $idCount - $notEnabledCount;
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully enabled', $enabledCount
                    )
                );
                if ($idCount != $enabledCount) {
                    Mage::getSingleton('adminhtml/session')->addWarning(
                        Mage::helper('adminhtml')->__(
                            'Total of %d record(s) were not enabled.'
                            .' ID(s) or rule(s) that could cause redirect loop:</br> '.$notEnabledIds.'', $notEnabledCount
                        )
                    );
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massDisableAction()
    {
        $ids = $this->getRequest()->getParam('redirect_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('seo/redirect')
                        ->load($id)
                        ->setIsActive(false);
                    $model->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully disabled', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    protected function _initModel()
    {
        $model = Mage::getModel('seo/redirect');
        if ($this->getRequest()->getParam('id')) {
            $model->load($this->getRequest()->getParam('id'));
        }

        Mage::register('current_redirect_model', $model);

        return $model;
    }

}