<?php

class Wyomind_Watchlogpro_Model_Watchlog extends Wyomind_Watchlog_Model_Watchlog {

    public function getSummary() {

        $collection = parent::getSummary();
        $collection->getSelect()->columns('SUM(IF(`type`=2,1,0)) as blocked');
        
        return $collection;
    }

}
