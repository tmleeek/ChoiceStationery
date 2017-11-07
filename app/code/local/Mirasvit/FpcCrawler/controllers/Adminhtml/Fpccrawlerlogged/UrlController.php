<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Full Page Cache
 * @version   1.0.5.3
 * @build     520
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_FpcCrawler_Adminhtml_Fpccrawlerlogged_UrlController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/fpc');
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system')
            ->_addBreadcrumb(Mage::helper('fpccrawler')->__('Full Page Cache'), Mage::helper('fpccrawler')->__('Full Page Cache'));

        return $this;
    }

    public function indexAction()
    {
        Mage::helper('fpc')->showFreeHddSpace(false, false);
        Mage::helper('fpc')->showExtensionDisabledInfo();
        Mage::helper('fpc')->showCronStatusError();
        Mage::getSingleton('adminhtml/session')->addNotice($this->_getCronInfo());

        $this->_title($this->__('Crawler URLs'));

        $this->_initAction();

        $this->_addContent($this->getLayout()->createBlock('fpccrawler/adminhtml_crawlerlogged_url'));

        $this->renderLayout();
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('url_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select urls(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('fpccrawler/crawlerlogged_url')
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

    public function massClearAction()
    {
        $ids = $this->getRequest()->getParam('url_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select urls(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('fpccrawler/crawlerlogged_url')
                        ->load($id);
                    $model->clearCache();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Cache for %d record(s) were successfully cleared.', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massWarmAction()
    {
        $ids = $this->getRequest()->getParam('url_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select urls(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('fpccrawler/crawlerlogged_url')
                        ->load($id);
                    $model->warmCache();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Cache for %d record(s) were successfully warmed.', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    protected function _getCronInfo()
    {
        $html = array();

        $html[] = $this->__('Last cron run time: <b>%s</b>', $this->_getLastCronTime(null));
        $html[] = $this->__('Last crawler job run time: <b>%s</b>', $this->_getLastCronTime('fpc_crawlerlogged'));
        $html[] = $this->__('Last URLs import run time: <b>%s</b>', $this->_getLastCronTime('fpc_log_import'));
        $html[] = $this->__('Last cache clear time (expired cache): <b>%s</b>', $this->_getLastCronTime('fpc_cache_clean_old'));

        return implode('<br>', $html);
    }

    protected function _getLastCronTime($jobCode)
    {
        $time = '-';

        $collection = Mage::getModel('cron/schedule')->getCollection()
            ->setOrder('executed_at', 'desc');
        if ($jobCode) {
            $collection->addFieldToFilter('job_code', $jobCode);
        }

        $collection->getSelect()->limit('1');
        $cron = $collection->getFirstItem();

        if ($cron->getExecutedAt()) {
            $time = Mage::getSingleton('core/date')->date('d.m.Y H:i', strtotime($cron->getExecutedAt()));
        }

        return $time;
    }
}
