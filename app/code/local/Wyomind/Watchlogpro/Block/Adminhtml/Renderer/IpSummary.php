<?php

class Wyomind_Watchlogpro_Block_Adminhtml_Renderer_IpSummary extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $ip = $row->getIp();
        $blacklist_tmp = Mage::helper('core')->jsonDecode(Mage::getStoreConfig("watchlogpro/settingspro/blacklist"));
        $blacklist = array();
        if (!is_array($blacklist_tmp))
            $blacklist_tmp = array();
        foreach ($blacklist_tmp as $bl) {
            $blacklist[] = $bl['ip'];
        }

        $whitelist = Mage::helper('core')->jsonDecode(Mage::getStoreConfig("watchlogpro/settingspro/whitelist"));
        if (!is_array($whitelist))
            $whitelist = array();
        if (in_array($ip, $whitelist)) {
            return "<span class='listed whitelisted' title='" . Mage::helper('watchlogpro')->__('Whitelisted') . "\n" . $this->__('Check this ip') . "'><span><a target='_blank' href='http://www.abuseipdb.com/check/" . $ip . "'>" . $ip . "</a></span></span>";
        } else if (in_array($ip, $blacklist)) {
            return "<span class='listed blacklisted' title='" . Mage::helper('watchlogpro')->__('Blacklisted') . "\n" . $this->__('Check this ip') . "'><span><a target='_blank' href='http://www.abuseipdb.com/check/" . $ip . "'>" . $ip . "</a></span></span>";
        } else {
            return "<span class='listed' title='" . $this->__('Check this ip') . "'><span><a target='_blank' href='http://www.abuseipdb.com/check/" . $ip . "'>" . $ip . "</a></span></span>";
        }
    }

}
