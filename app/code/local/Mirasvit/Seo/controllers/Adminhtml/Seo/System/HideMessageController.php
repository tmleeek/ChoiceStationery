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



class Mirasvit_Seo_Adminhtml_Seo_System_HideMessageController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/seo');
    }

    public function updateAction()
    {
        $request = Mage::app()->getRequest();
        $isChecked = $request->getParam('checked');
        $section = $request->getParam('section');
        if ($isChecked) {
            $this->setVariableValue(0, $section);
        } else {
            $this->setVariableValue(1, $section);
        }
    }

    protected function setVariableValue($value, $section)
    {
        $model = Mage::getModel('core/variable');
        switch($section) {
            case 'seo':
                $model->loadByCode(Mirasvit_Seo_Model_Config::SEO_POST_INSTALL_MESSAGE)
                      ->setCode(Mirasvit_Seo_Model_Config::SEO_POST_INSTALL_MESSAGE)
                      ->setName('Show SEO Post-installation Message')
                      ->setPlainValue($value)
                      ->save();
                break;
            case 'seoautolink':
                $model->loadByCode(Mirasvit_Seo_Model_Config::AUTOLINK_POST_INSTALL_MESSAGE)
                      ->setCode(Mirasvit_Seo_Model_Config::AUTOLINK_POST_INSTALL_MESSAGE)
                      ->setName('Show SEO Auto Links Post-installation Message')
                      ->setPlainValue($value)
                      ->save();
                break;
            case 'seositemap':
                $model->loadByCode(Mirasvit_Seo_Model_Config::SEOSITEMAP_POST_INSTALL_MESSAGE)
                      ->setCode(Mirasvit_Seo_Model_Config::SEOSITEMAP_POST_INSTALL_MESSAGE)
                      ->setName('Show SEO Site Map Post-installation Message')
                      ->setPlainValue($value)
                      ->save();
                break;
        }
    }
}