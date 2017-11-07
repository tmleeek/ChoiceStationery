<?php

class Wyomind_Watchlogpro_Block_Adminhtml_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {

    public function render(Varien_Object $row) {
        $actions = array();

        $blacklist_tmp = Mage::helper('core')->jsonDecode(Mage::getStoreConfig("watchlogpro/settingspro/blacklist"));
        $blacklist = array();
        if (!is_array($blacklist_tmp))
            $blacklist_tmp = array();
        foreach ($blacklist_tmp as $bl) {
            $blacklist[] = $bl['ip'];
        }
        $whitelist = Mage::helper('core')->jsonDecode(Mage::getStoreConfig("watchlogpro/settingspro/whitelist"));
        
        $current_ip = Mage::helper('core/http')->getRemoteAddr();
        
        $ip = $row->getIp();

        if (!is_array($blacklist))
            $blacklist = array();
        if (!is_array($whitelist))
            $whitelist = array();
        if (!in_array($ip, $blacklist) && !in_array($ip, $whitelist)&& $ip != $current_ip) { // you cannot blacklist yourself !!
            $actions[] = array(
                'url' => $this->getUrl('adminhtml/watchlogpro/blacklist', array('ip' => $ip)),
                'confirm' => Mage::helper('watchlogpro')->__("Please confirm that you want to add the IP '" . $ip . "' to the blacklist"),
                'caption' => Mage::helper('watchlogpro')->__("Add IP to the blacklist"),
            );
        }

        if (in_array($ip, $blacklist) && !in_array($ip, $whitelist)) {
            $actions[] = array(
                'url' => $this->getUrl('adminhtml/watchlogpro/unblacklist', array('ip' => $ip)),
                'confirm' => Mage::helper('watchlogpro')->__("Please confirm that you want to remove the IP '" . $ip . "' from the blacklist"),
                'caption' => Mage::helper('watchlogpro')->__("Remove IP from the blacklist"),
            );
        }

        if (!in_array($ip, $whitelist) && !in_array($ip, $blacklist)) {
            $actions[] = array(
                'url' => $this->getUrl('adminhtml/watchlogpro/whitelist', array('ip' => $ip)),
                'confirm' => Mage::helper('watchlogpro')->__("Please confirm that you want to add the IP '" . $ip . "' to the whitelist"),
                'caption' => Mage::helper('watchlogpro')->__("Add IP to the whitelist"),
            );
        }

        if (in_array($ip, $whitelist) && !in_array($ip, $blacklist)) {
            $actions[] = array(
                'url' => $this->getUrl('adminhtml/watchlogpro/unwhitelist', array('ip' => $ip)),
                'confirm' => Mage::helper('watchlogpro')->__("Please confirm that you want to remove the IP '" . $ip . "'  from the whitelist"),
                'caption' => Mage::helper('watchlogpro')->__("Remove IP from the whitelist"),
            );
        }

        $this->getColumn()->setActions($actions);
        return parent::render($row);
    }

}
