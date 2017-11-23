<?php
/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
class Wyomind_Watchlogpro_Block_Adminhtml_Renderer_Ip extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) 
    {
        $ip = $row->getIp();
        $status = $row->getIpStatus();
        $watchlogProHelper = Mage::helper('watchlogpro');
        $class = '';
        $title = '';
        
        if ($status == 0) {
            $class = 'whitelisted';
            $title = $watchlogProHelper->__('Whitelisted') . "\n";
        }
        if ($status == 1) {
            $class = 'blacklisted';
            $title = $watchlogProHelper->__('Blacklisted') . "\n";
        }
        
        return "<span class='listed " . $class . "' title='" . $title . $watchlogProHelper->__('Check this ip') . "'>"
                . "<span><a target='_blank' href='http://www.abuseipdb.com/check/" . $ip . "'>" . $ip . "</a></span>"
                . "</span>";
    }
}