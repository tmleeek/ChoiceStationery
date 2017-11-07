<?php
class Rock_Ipad_Model_Mysql4_Ipad extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("ipad/ipad", "id");
    }
}