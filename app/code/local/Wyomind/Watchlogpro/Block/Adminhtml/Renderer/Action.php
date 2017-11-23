<?php
/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
class Wyomind_Watchlogpro_Block_Adminhtml_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        $actions = array();
        $blacklistTmp = array();
        
        $coreHelper = Mage::helper('core');
        $watchlogProHelper = Mage::helper('watchlogpro');
        
        if (Mage::getStoreConfig('watchlogpro/settingspro/whitelist') != '') {
            $blacklistTmp = $coreHelper->jsonDecode(Mage::getStoreConfig('watchlogpro/settingspro/blacklist'));
        }

        $blacklist = array();
        if (!is_array($blacklistTmp)) {
            $blacklistTmp = array();
        }
        
        foreach ($blacklistTmp as $bl) {
            $blacklist[] = $bl['ip'];
        }
        
        $whitelist = array();
        if (Mage::getStoreConfig('watchlogpro/settingspro/whitelist') != '') {
            $whitelist = $coreHelper->jsonDecode(Mage::getStoreConfig('watchlogpro/settingspro/whitelist'));
        }

        $currentIp = Mage::helper('core/http')->getRemoteAddr();
        $ip = $row->getIp();
        
        // Not yet blacklisted and not whitelisted + you cannot blacklist yourself
        if (false === $watchlogProHelper->isListed($ip, $blacklist)
            && false === $watchlogProHelper->isListed($ip, $whitelist) && $ip != $currentIp) {
            $actions[] = array(
                'url'       => $this->getUrl('adminhtml/watchlogpro/blacklist', array('ip' => $ip)),
                'confirm'   => $watchlogProHelper->__("Please confirm that you want to add the IP '" . $ip . "' to the blacklist"),
                'caption'   => $watchlogProHelper->__('Add IP to the blacklist')
            );
        }
        
        // Blacklisted and not whitelisted
        if (in_array($ip, $blacklist) && false === $watchlogProHelper->isListed($ip, $whitelist)) {
            $actions[] = array(
                'url'       => $this->getUrl('adminhtml/watchlogpro/unblacklist', array('ip' => $ip)),
                'confirm'   => $watchlogProHelper->__("Please confirm that you want to remove the IP '" . $ip . "' from the blacklist"),
                'caption'   => $watchlogProHelper->__('Remove IP from the blacklist')
            );
        }

        // Not yet whitelisted and not blacklisted
        if (false === $watchlogProHelper->isListed($ip, $whitelist) 
            && false === $watchlogProHelper->isListed($ip, $blacklist)) {
            $actions[] = array(
                'url'       => $this->getUrl('adminhtml/watchlogpro/whitelist', array('ip' => $ip)),
                'confirm'   => $watchlogProHelper->__("Please confirm that you want to add the IP '" . $ip . "' to the whitelist"),
                'caption'   => $watchlogProHelper->__('Add IP to the whitelist')
            );
        }

        // Whitelisted and not blacklisted
        if (in_array($ip, $whitelist) && false === $watchlogProHelper->isListed($ip, $blacklist)) {
            $actions[] = array(
                'url'       => $this->getUrl('adminhtml/watchlogpro/unwhitelist', array('ip' => $ip)),
                'confirm'   => $watchlogProHelper->__("Please confirm that you want to remove the IP '" . $ip . "'  from the whitelist"),
                'caption'   => $watchlogProHelper->__('Remove IP from the whitelist')
            );
        }

        $this->getColumn()->setActions($actions);
        
        return parent::render($row);
    }
}