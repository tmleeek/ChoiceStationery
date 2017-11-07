<?php

class Potato_Compressor_Model_Resource_Image_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('po_compressor/image');
    }
}