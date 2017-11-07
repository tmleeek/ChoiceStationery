<?php

class MDN_Quotation_Helper_MagentoVersionCompatibility extends Mage_Core_Helper_Abstract {

    public function createStockItemForBundle() {
        switch ($this->getVersionMinor()) {
            case '1.4.2':
                return false;
                break;
            default:
                return true;
                break;
        }
    }

    /**
     * return version
     *
     * @return unknown
     */
    private function getVersion() {
        $version = Mage::getVersion();
        $t = explode('.', $version);
        return $t[0] . '.' . $t[1];
    }

    /**
     * return version
     *
     * @return unknown
     */
    private function getVersionMinor() {
        $version = Mage::getVersion();
        $t = explode('.', $version);
        return $t[0] . '.' . $t[1] . '.' . $t[2];
    }

}