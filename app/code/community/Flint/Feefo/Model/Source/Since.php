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
class Flint_Feefo_Model_Source_Since
{

    protected $_options;

    public function toOptionArray() {
        if( !$this->_options ) {
            $options_default = array(
                array( 'value' => 'all', 'label' => Mage::helper( 'flint_feefo' )->__( 'All' ) ),
                array( 'value' => 'year', 'label' => Mage::helper( 'flint_feefo' )->__( 'One year' ) ),
                array( 'value' => '6month', 'label' => Mage::helper( 'flint_feefo' )->__( '6 months' ) ),
                array( 'value' => 'month', 'label' => Mage::helper( 'flint_feefo' )->__( 'One month' ) ),
                array( 'value' => 'week', 'label' => Mage::helper( 'flint_feefo' )->__( 'One week' ) ),
            );
            $this->_options = $options_default;
        }

        return $this->_options;
    }

}
