<?php
/**
 * Flint Technology Ltd
 *
 * This module was developed by Flint Technology Ltd (http://www.flinttechnology.co.uk).
 * For support or questions, contact us via feefo@flinttechnology.co.uk 
 * Support website: https://www.flinttechnology.co.uk/support/projects/feefo/
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA bundled with this package in the file LICENSE.txt.
 * It is also available online at http://www.flinttechnology.co.uk/store/module-license-1.0
 *
 * @package     flint_feefo-ce-2.0.5.zip
 * @registrant  Paul Andrews, Choice Stationery Supplies
 * @license     FFFEA83A-B2B2-4E66-B4F5-AE27E326AAC3
 * @eula        Flint Module Single Installation License (http://www.flinttechnology.co.uk/store/module-license-1.0
 * @copyright   Copyright (c) 2014 Flint Technology Ltd (http://www.flinttechnology.co.uk)
 */
?>
<?php
abstract class Flint_Feefo_Model_Config_Abstract extends Varien_Object
{

    //
    //  Path to config section within system.xml (e.g. shutl_shipping/general)
    //
    protected $_defaultPath = '';

    protected function unique( $storeId, $key ) {
        return ( ( is_null( $storeId ) ) ? "null" : $storeId ) . "_" . $key;
    }

    protected function clean( $path ) {
        do {
            $old = $path;
            $path = str_replace( '//', '/', $path );
        } while( $old != $path );
        return $path;
    }

    public function getConfigData( $storeId, $key, $default = false, $path = null ) {
        if( is_null( $storeId ) ) {
            $storeId = Mage::app()->getStore()->getId();
        }
        $unique = $this->unique( $storeId, $key );
        if( !$this->hasData( $unique ) ) {
            if( is_null( $path ) ) {
                $path = $this->_defaultPath;
            }
            $pathKey = $this->clean( $path . '/' . $key );
            $value = Mage::getStoreConfig( $pathKey, $storeId );
            if( is_null( $value ) || false === $value ) {
                $value = $default;
            }
            $this->setData( $unique, $value );
        }
        return $this->getData( $unique );
    }

}
