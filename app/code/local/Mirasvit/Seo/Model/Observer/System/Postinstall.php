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



class Mirasvit_Seo_Model_Observer_System_Postinstall extends Varien_Debug 
{
    protected $_section;
    protected $_seoVersion;
	
	public function displayMessage()
    {
        if (!($request = Mage::app()->getRequest()) || $_POST) {
            return;
        }

        $this->_section = $request->getParam('section');
        if ($this->showMessage()) {                
            $this->_seoVersion = Mage::helper('seo/version')->getCurrentSeoSuiteVersion();
            
            $guide = $this->getGuideLocation();
            $configInfo = $this->getInfoAssembled();
            $doNotShowBox = $this->getCheckboxScript();
            
            $message = $guide . $configInfo. $doNotShowBox;
            Mage::getSingleton('adminhtml/session')->addNotice($message);
        }
    }

    private function showMessage()
    {
        if ($this->_section == 'seo' 
            && Mage::getModel('core/variable')->loadByCode(Mirasvit_Seo_Model_Config::SEO_POST_INSTALL_MESSAGE)->getValue()) {
            return true;
        } elseif ($this->_section == 'seoautolink'
            && Mage::getModel('core/variable')->loadByCode(Mirasvit_Seo_Model_Config::AUTOLINK_POST_INSTALL_MESSAGE)->getValue()) {
            return true;
        } elseif ($this->_section == 'seositemap'
            && Mage::getModel('core/variable')->loadByCode(Mirasvit_Seo_Model_Config::SEOSITEMAP_POST_INSTALL_MESSAGE)->getValue()) {
            return true;
        }
        return false;
    }

    public function getInfoAssembled()
    {
        switch ($this->_section) {
            case 'seo':
                $configText = $this->getSeoConfigInfo() . $this->getExampleData();
                break;
            case 'seositemap':
                $configText = $this->getSitemapConfigInfo();
                break;
            case 'seoautolink':
                $configText = $this->getAutolinkConfigInfo();
                break;
        }
        return $configText;
    }

    private function getSeoConfigInfo()
    {
        $seoManualPath = '/SEO_configuration/settings';
        $manualPageText = 'More information on SEO configuration options you may get 
        <a target="_blank" href="https://mirasvit.com/doc/seo/'. $this->_seoVersion 
        . $seoManualPath.'/">here</a> ';

        return $manualPageText;
    }

    private function getAutolinkConfigInfo()
    {
        $seoAutolinkManualPath = '/auto_links_configuration/auto_links';
        $manualPageText = 'More information on Auto Links configuration options you may get 
        <a target="_blank" href="https://mirasvit.com/doc/seo/'. $this->_seoVersion 
        . $seoAutolinkManualPath.'/">here</a> ';

        return $manualPageText;
    }

    private function getSitemapConfigInfo()
    {
        $seoSitemapManualPath = '/extended_sitemap_configuration/backend_site_map';
        $manualPageText = 'More information on Extended Site Map configuration options you may get 
        <a target="_blank" href="https://mirasvit.com/doc/seo/'. $this->_seoVersion 
        . $seoSitemapManualPath.'/">here</a> ';

        return $manualPageText;
    }

    private function getExampleData()
    {
        $seoTemplatesConfigPage = Mage::helper("adminhtml")->getUrl('*/seo_template/index/');;
        $seoTemplatesManualPath = '/SEO_configuration/SEO_templates_management';
        $reditectsConfigPage = Mage::helper("adminhtml")->getUrl('*/seo_redirect/index/');;
        $redirectManualPath = '/SEO_configuration/redirects';

        $exampleDataText = '<br/>' . 'We recommend checking example SEO Templates in <a target="_blank" href="'
        . $seoTemplatesConfigPage . '"> SEO > SEO Templates </a>'
        .'More information on SEO Templates management you can get <a target="_blank" href="https://mirasvit.com/doc/seo/'
        . $this->_seoVersion . $seoTemplatesManualPath.'/">here</a>. '
        .'<br/>'.'It may also be a good idea to check out an example redirect rule set up for you in <a target="_blank" href="'
        . $reditectsConfigPage . '"> SEO > Redirect > Management </a>. '
        .'More info about this functionality you can get <a target="_blank" href="https://mirasvit.com/doc/seo/'
        . $this->_seoVersion . $redirectManualPath.'/">here</a>';

        return $exampleDataText;

    }

    private function getGuideLocation()
    {
        return 'To get started with the extension we recommend checking out our step-by-step guide here:
        <a target="_blank" href="https://mirasvit.com/doc/seo/'
        . $this->_seoVersion . '/post_install'.'/">"Post-installation Guide"</a><br/>';
    }

    private function getCheckboxScript()
    {
        $checkbox = '<div>'. 'Don\'t show again <input onchange="callSeoHideMessageController(this)" 
        type="checkbox" name="seo_hide_message" class="massaction-checkbox"></div>';

        $script = '<script type="text/javascript">function callSeoHideMessageController(e)
                {
                    var isChecked = (e.checked == true) ? 1 : 0;
                    var section = "'.$this->_section.'";
                    new Ajax.Request("' . Mage::helper('adminhtml')->getUrl('*/seo_system_hideMessage/update') . '" , {
                        method: "Post",
                        parameters: {"checked":isChecked,"section":section},
                        // onComplete: function(transport) {
                        //     alert(transport.responseText);
                        // }
                    });
                } </script>';
        return $checkbox . $script;
    }

}