<?php
/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
class Wyomind_Watchlogpro_Helper_Http extends Mage_Core_Helper_Http
{
    public function authValidate($headers = null)
    {
        $observer = new Wyomind_Watchlogpro_Model_Observer();
        $observer->checkIP(null);
        
        return parent::authValidate($headers);
        
    }
}