<?php

class Potato_Compressor_Model_Image extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('po_compressor/image');
    }

    public function loadByHash($hash)
    {
        return $this->load($hash , 'hash');
    }
}