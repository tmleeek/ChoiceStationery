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
class Flint_Feefo_Model_Source_Product_Attributes
{

    protected $_attributes = false;
    protected $_all = false;
    protected $_with_options = false;

    protected function getAttributes() {
        if( !$this->_attributes ) {
            $this->_attributes = Mage::getResourceModel( 'catalog/product_attribute_collection' );
        }
        return $this->_attributes;
    }

    public function toOptionArray() {
        if( !$this->_all ) {
            $result = array();
            foreach( $this->getAttributes() as $attribute ) {
                $result[] = array(
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getAttributeCode()
                );
            }
            array_unshift( $result, array( 'value' => '',
                'label' => Mage::helper( 'flint_feefo' )->__( '--' ) ) );
            $this->_all = $result;
        }
        return $this->_all;
    }

    public function filterWithOptions() {
        if( !$this->_with_options ) {
            $result = array();
            foreach( $this->getAttributes() as $attribute ) {
                if( $attribute->usesSource() ) {
                    $result[] = array(
                        'value' => $attribute->getAttributeCode(),
                        'label' => $attribute->getAttributeCode()
                    );
                }
            }
            $this->_with_options = $result;
        }
        return $this->_with_options;
    }

}
