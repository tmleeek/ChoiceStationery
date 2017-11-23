<?php
/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
class Wyomind_Watchlogpro_Model_Resource_Watchlogpro extends Wyomind_Watchlog_Model_Resource_Watchlog
{
    public function getSummary() 
    {
        $collection = parent::getSummary();
        $collection->getSelect()
                    ->columns('SUM(IF(`type`=2,1,0)) as blocked');
        
        return $collection;
    }
    
    public function getLastAttempt($ip)
    {
        $collection = Mage::getModel('watchlog/watchlog')->getCollection();
        $collection->getSelect()
                    ->columns('MAX(date) AS date')
                    ->where("ip = '" . $ip . "' AND `type` > 0");
        
        return $collection;
    }
    
    public function getNbAttempt($date, $ip)
    {
        $collection = Mage::getModel('watchlog/watchlog')->getCollection();
        $collection->getSelect()
                    ->columns('COUNT(watchlog_id) AS nb')
                    ->where("date >= '" . $date . "' AND ip='" . $ip . "' AND `type`=0");
        
        return $collection;
    }
}