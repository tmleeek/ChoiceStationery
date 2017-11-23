<?php
/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
class Wyomind_Watchlogpro_Block_Adminhtml_Renderer_IpSummary extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) 
    {
        $ip = $row->getIp();
        $coreHelper = Mage::helper('core');
        $watchlogProHelper = Mage::helper('watchlogpro');
        $blacklist = array();
        $whitelist = array();
        $blacklistTmp = array();
        $class = '';
        $title = '';
        
        if (Mage::getStoreConfig('watchlogpro/settingspro/blacklist') != '') {
            $blacklistTmp = $coreHelper->jsonDecode(Mage::getStoreConfig('watchlogpro/settingspro/blacklist'));
        }
        
        foreach ($blacklistTmp as $bl) {
            $blacklist[] = $bl['ip'];
        }
        
        if (Mage::getStoreConfig('watchlogpro/settingspro/whitelist') != '') {
            $whitelist = $coreHelper->jsonDecode(Mage::getStoreConfig("watchlogpro/settingspro/whitelist"));
        }
        
        if ($watchlogProHelper->isListed($ip, $whitelist)) {
            $class = 'whitelisted';
            $title = $watchlogProHelper->__('Whitelisted') . "\n";
        }
        if ($watchlogProHelper->isListed($ip, $blacklist)) {
            $class = 'blacklisted';
            $title = $watchlogProHelper->__('Blacklisted') . "\n";
        }
        
        return "<span class='listed " . $class . "' title='" . $title . $watchlogProHelper->__('Check this ip') . "'>"
                . "<span><a target='_blank' href='http://www.abuseipdb.com/check/" . $ip . "'>" . $ip . "</a></span>"
                . "</span>";
    }
}