<?php
/**
 * Pricerules Group Collection
 *
 * @author Stock in the Channel
 */
class Sinch_Pricerules_Model_Resource_Group_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

    protected function _construct(){
        $this->_init('sinch_pricerules/group');
    }
}