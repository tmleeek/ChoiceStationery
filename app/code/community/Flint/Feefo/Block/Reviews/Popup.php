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
class Flint_Feefo_Block_Reviews_Popup extends Flint_Feefo_Block_Reviews_Abstract
{

    public function isEnabled() {
        return true;
    }

    public function getLogon() {
        if( $this->getBusinessCategory() ) {
            return parent::getLogon().$this->getBusinessCategory();
        } else {
            return parent::getLogon();
        }
    }

    public function getSku() {
        return $this->decodeSku( $this->getRequest()->getParam( 'vendorref' ) );
    }

    public function getBusinessCategory() {
        return $this->getRequest()->getParam( 'category' );
    }

    public function getMode() {
        return $this->getRequest()->getParam( 'mode' );
    }

    public function getForfeedback() {
        return $this->getRequest()->getParam( 'forfeedback' );
    }

    public function getOrder() {
        return $this->getRequest()->getParam( 'order' );
    }

    public function getSince() {
        return $this->getRequest()->getParam( 'since' );
    }

    public function getLimit() {
        return $this->getRequest()->getParam( 'limit' );
    }

    public function getAdditional() {
        return $this->getRequest()->getParam( 'additional' );
    }

    public function getCssId() {
        return 'product_reviews_popup';
    }

}
