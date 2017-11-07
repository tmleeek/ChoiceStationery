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
 * @package     flint_feefo-ce-2.0.13.zip
 * @registrant  Paul Andrews, Choice Stationery Supplies
 * @license     FFFEA83A-B2B2-4E66-B4F5-AE27E326AAC3
 * @eula        Flint Module Single Installation License (http://www.flinttechnology.co.uk/store/module-license-1.0
 * @copyright   Copyright (c) 2014 Flint Technology Ltd (http://www.flinttechnology.co.uk)
 */
?>
<?php
class Flint_Feefo_Model_Source_Service_Position
{

    protected $_options;

    public function toOptionArray() {
        if( !$this->_options ) {
            $options_default = array(
                array( 'value' => 'feefo_logo_service_right', 'label' => Mage::helper( 'flint_feefo' )->__( 'Default - Right column block' ) ),
                array( 'value' => 'feefo_logo_service_left', 'label' => Mage::helper( 'flint_feefo' )->__( 'Default - Left column block' ) ),
                array( 'value' => 'null', 'label' => Mage::helper( 'flint_feefo' )->__( '' ) ),
                array( 'value' => 'feefo_basket_service_proceed', 'label' => Mage::helper( 'flint_feefo' )->__( 'Basket page - Before "Proceed to Checkout"' ) ),
                array( 'value' => 'feefo_basket_service_abovelist', 'label' => Mage::helper( 'flint_feefo' )->__( 'Basket page - Above product list' ) ),
                array( 'value' => 'feefo_basket_service_total', 'label' => Mage::helper( 'flint_feefo' )->__( 'Basket page - Total block' ) ),
                array( 'value' => 'null', 'label' => Mage::helper( 'flint_feefo' )->__( '' ) ),
                array( 'value' => 'feefo_onepage_right_top', 'label' => Mage::helper( 'flint_feefo' )->__( 'Onepage Checkout - Right column (top)' ) ),
                array( 'value' => 'feefo_onepage_review_after', 'label' => Mage::helper( 'flint_feefo' )->__( 'Onepage Checkout - Order review' ) ),
            );
            $this->_options = $options_default;
        }

        return $this->_options;
    }

}
