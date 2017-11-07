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
class Flint_Feefo_Helper_Reviews extends Flint_Feefo_Helper_Data
{

    public function getReviews( $url ) {
        $cacheLiveTime = Mage::getSingleton( 'flint_feefo/config_product_reviews' )->getCaching();

        $reviewsXML = Mage::app()->loadCache( $url );

        if( !$reviewsXML || !$cacheLiveTime ) {
            $reviewsXML = $this->loadReviewsXML( $url );
            Mage::app()->saveCache( $reviewsXML, $url, array( 'FLINT_FEEFO' ), $cacheLiveTime );

            if( !$reviews = $this->createSimpleXML( $reviewsXML ) ) {
                return false;
            }
            return $reviews;
        } else {
            if( !$reviews = $this->createSimpleXML( $reviewsXML ) ) {
                return false;
            }
            return $reviews;
        }
    }

    protected function loadReviewsXML( $url ) {
        try {
            $crl = curl_init();
            $timeout = 2;
            curl_setopt( $crl, CURLOPT_URL, $url );
            curl_setopt( $crl, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $crl, CURLOPT_LOW_SPEED_TIME, 2 );
            curl_setopt( $crl, CURLOPT_CONNECTTIMEOUT, $timeout );
            $ret = curl_exec( $crl );
            curl_close( $crl );
            return $ret;
        } catch( Exception $exc ) {
            
        }
    }

    protected function createSimpleXML( $xml_string ) {
        try {
            return new SimpleXMLElement( $xml_string );
        } catch( Exception $ex ) {
            return false;
        }
    }

}
