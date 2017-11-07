<?php

class MDN_CrmTicket_Model_RouterRules extends Mage_Core_Model_Abstract {

    public function _construct() {

        $this->_init('CrmTicket/RouterRules', 'crr_id');
    }
}