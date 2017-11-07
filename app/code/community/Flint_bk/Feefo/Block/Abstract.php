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
abstract class Flint_Feefo_Block_Abstract extends Mage_Core_Block_Template
{

    const LOGO_BASE_URL = 'https://www.feefo.com/feefo/feefologo.jsp';
    const FEEFO_SLASH_GUID = 'f9d4d816-edd3-48a3-bc60-dde8291e63bb';

    public function getLogoSrc() {
        $data = array(
            'logon' => $this->getLogon(),
            'template' => $this->getLogoTemplate(),
            'vendorref' => $this->getSku(),
            'mode' => $this->getMode(),
            'forfeedback' => $this->getForfeedback(),
            'order' => $this->getOrder(),
            'since' => $this->getSince(),
            'limit' => $this->getLimit(),
        );

        return self::LOGO_BASE_URL . '?' . http_build_query( $data ) . $this->getAdditional();
    }

    public function getLogoLink() {
        $data = array(
            '_escape' => true,
            //'logon' => urlencode( $this->getLogon() ),
            'vendorref' => urlencode( $this->encodeSku( $this->getSku() ) ),
            'mode' => $this->getMode(),
            //'forfeedback' => $this->getForFeedback(),
            'order' => $this->getOrder(),
            'since' => $this->getSince(),
            'limit' => $this->getLimit(),
            'category' => urlencode( $this->getBusinessCategory() ),
        );


        return Mage::getUrl( 'flint_feefo/popup', $data );
    }

    public function getLogon() {
        return $this->getConfigGeneral()->getLogon();
    }

    public function getSku() {
        return null;
    }

    public function getConfigGeneral() {
        return Mage::getSingleton( 'flint_feefo/config_general' );
    }

    public function getConfigService() {
        return Mage::getSingleton( 'flint_feefo/config_service' );
    }

    public function getConfigProduct() {
        return Mage::getSingleton( 'flint_feefo/config_product' );
    }

    public function getConfigProductReviews() {
        return Mage::getSingleton( 'flint_feefo/config_product_reviews' );
    }

    abstract public function getLogoTemplate();

    abstract public function getMode();

    abstract public function getForfeedback();

    abstract public function getOrder();

    abstract public function getSince();

    abstract public function getLimit();

    abstract public function getAdditional();

    public function getBusinessCategory() {
        return null;
    }

    public function getCssId() {
        return 'flint_feefo';
    }

    public function encodeSku( $sku ) {
        return str_replace( '/', self::FEEFO_SLASH_GUID, $sku );
    }

    public function decodeSku( $sku ) {
        return str_replace( self::FEEFO_SLASH_GUID, '/', $sku );
    }

}
