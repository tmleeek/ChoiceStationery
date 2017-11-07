<?php

class Potato_Compressor_Model_Resource_Image extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('po_compressor/image', 'id');
    }

    public function truncate()
    {
        $this->_getWriteAdapter()->truncate($this->getMainTable());
        return $this;
    }
}