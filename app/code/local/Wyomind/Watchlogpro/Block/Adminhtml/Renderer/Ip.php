<?php


class Wyomind_Watchlogpro_Block_Adminhtml_Renderer_Ip extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    
    public function render(Varien_Object $row) {
        
        $ip = $row->getIp();
        $status = $row->getIpStatus();
        
        if ($status==0) {
            return "<span class='listed whitelisted' title='".Mage::helper('watchlogpro')->__('Whitelisted')."\n".$this->__('Check this ip')."'><span><a target='_blank' href='http://www.abuseipdb.com/check/".$ip."'>".$ip."</a></span></span>";
        } else if ($status==1) {
            return "<span class='listed blacklisted' title='".Mage::helper('watchlogpro')->__('Blacklisted')."\n".$this->__('Check this ip')."'><span><a target='_blank' href='http://www.abuseipdb.com/check/".$ip."'>".$ip."</a></span></span>";
        } else {
            return "<span class='listed' title='".$this->__('Check this ip')."'><span><a target='_blank' href='http://www.abuseipdb.com/check/".$ip."'>".$ip."</a></span></span>";
        }
    }
    
}
