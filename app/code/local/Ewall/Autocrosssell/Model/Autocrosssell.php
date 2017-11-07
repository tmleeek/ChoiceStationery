<?php
class Ewall_Autocrosssell_Model_Autocrosssell extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('autocrosssell/autocrosssell', 'product_id');
    }

    public function getRelatedArray()
    {
        $relatedArray = $this->getData('related_array');
        if ($relatedArray && is_string($relatedArray)) {
            $relatedArray = unserialize($relatedArray);
        } else {
            $relatedArray = array();
        }
        return $relatedArray;
    }
}
