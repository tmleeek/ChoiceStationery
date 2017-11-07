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
class Flint_Feefo_Model_Source_Product_Position
{

    protected $_options;

    public function toOptionArray() {
        if( !$this->_options ) {
            $options_default = array(
                array( 'value' => 'feefo_logo_extrahint', 'label' => Mage::helper( 'flint_feefo' )->__( 'Product page - Extrahint block' ) ),
                array( 'value' => 'feefo_logo_product_right', 'label' => Mage::helper( 'flint_feefo' )->__( 'Product page - Right column block' ) ),
                array( 'value' => 'feefo_logo_product_left', 'label' => Mage::helper( 'flint_feefo' )->__( 'Product page - Left column block' ) ),
                array( 'value' => 'feefo_logo_alerturls', 'label' => Mage::helper( 'flint_feefo' )->__( 'Product page - Alert urls block' ) ),
                array( 'value' => 'feefo_logo_addtocart', 'label' => Mage::helper( 'flint_feefo' )->__( 'Product page - Addto cart block' ) ),
                array( 'value' => 'null', 'label' => Mage::helper( 'flint_feefo' )->__( '' ) ),
                array( 'value' => 'feefo_category_product_logo', 'label' => Mage::helper( 'flint_feefo' )->__( 'Category page - Product list' ) ),
                array( 'value' => 'null', 'label' => Mage::helper( 'flint_feefo' )->__( '' ) ),
                array( 'value' => 'feefo_logo_product_business_right', 'label' => Mage::helper( 'flint_feefo' )->__( 'Category page - Business Category(Right col.)' ) ),
                array( 'value' => 'feefo_logo_product_business_left', 'label' => Mage::helper( 'flint_feefo' )->__( 'Category page - Business Category(Left col.)' ) ),
                array( 'value' => 'null', 'label' => Mage::helper( 'flint_feefo' )->__( '' ) ),
                array( 'value' => 'feefo_basket_product_logo', 'label' => Mage::helper( 'flint_feefo' )->__( 'Basket page - Product list' ) ),
            );
            $this->_options = $options_default;
        }

        return $this->_options;
    }

}
