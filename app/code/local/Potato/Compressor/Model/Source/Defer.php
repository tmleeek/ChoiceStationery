<?php

class Potato_Compressor_Model_Source_Defer
{
    const DO_NOT_USE_VALUE       = 0;
    const ATTRIBUTE_VALUE        = 1;
    const MOVE_TO_BODY_END_VALUE = 2;

    const DO_NOT_USE_LABEL       = 'Don\'t use';
    const ATTRIBUTE_LABEL        = 'Add "Defer" attribute and merge inline script';
    const MOVE_TO_BODY_END_LABEL = 'Move all scripts in the end of the HTML body (recommended)';

    public function toOptionArray()
    {
        return array(
            self::MOVE_TO_BODY_END_VALUE => Mage::helper('po_compressor')->__(self::MOVE_TO_BODY_END_LABEL),
            self::ATTRIBUTE_VALUE        => Mage::helper('po_compressor')->__(self::ATTRIBUTE_LABEL),
            self::DO_NOT_USE_VALUE       => Mage::helper('po_compressor')->__(self::DO_NOT_USE_LABEL),
        );
    }
}