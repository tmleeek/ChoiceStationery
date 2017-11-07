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
class Flint_Feefo_Model_Source_Mode
{

    protected $_options;

    public function toOptionArray() {
        if( !$this->_options ) {
            $options_default = array(
                array( 'value' => '0', 'label' => Mage::helper( 'flint_feefo' )->__( '' ) ),
                array( 'value' => 'service', 'label' => Mage::helper( 'flint_feefo' )->__( 'Service Only Comments' ) ),
                array( 'value' => 'product', 'label' => Mage::helper( 'flint_feefo' )->__( 'Product Only Comments' ) ),
                array( 'value' => 'both', 'label' => Mage::helper( 'flint_feefo' )->__( 'Service and Product Comments' ) ),
                array( 'value' => 'prodco', 'label' => Mage::helper( 'flint_feefo' )->__( 'Excellent or Good Product Comments Only' ) ),
            );
            $this->_options = $options_default;
        }

        return $this->_options;
    }

}
