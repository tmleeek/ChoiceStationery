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
class Flint_Feefo_Block_Logo_Product_Category_Business extends Flint_Feefo_Block_Logo_Product_Abstract
{

    public function isEnabled() {
    if(!$this->getCurrentCategory() || !$this->getBusinessCategory()){
            return false;
        }
        return ( $this->getConfigProduct()->active() && in_array( $this->getNameInLayout(), explode( ',', $this->getConfigProduct()->getLogoPosition() ) ) );
    }

    public function getLogon() {
        return $this->getConfigGeneral()->getLogon().$this->getBusinessCategory();
    }

    public function getSku() {
        return '';
    }

    public function getBusinessCategory(){
        return '/'.Mage::app()->getStore()->getCode().'/'.$this->getCurrentCategory()->getFeefoBusinessCategory();
    }
    
    public function getCurrentCategory(){
        return Mage::registry('current_category');
    }

    public function getLogoTemplate() {
        return $this->getConfigProduct()->getLogoTemplateProduct();
    }

    public function getProduct() {
        return $this->getData( 'product' );
    }

    public function getCssId() {
        return 'flint_feefo_product_category_business';
    }

}
