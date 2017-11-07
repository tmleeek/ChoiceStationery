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
class Flint_Feefo_OrdersController extends Mage_Core_Controller_Front_Action
{

    public function indexAction() {
        $helper = Mage::helper( 'flint_feefo/feeds' );

        /*
         * Key parameter check
         */
        if( Mage::getSingleton( 'flint_feefo/config_general' )->getKey() && (!$this->getRequest()->getParam( 'k' ) || $this->getRequest()->getParam( 'k' ) != Mage::getSingleton( 'flint_feefo/config_general' )->getKey()) ) {
            $this->_forward( '404' );
            return;
        }
        /*
         * IP Firewall chack
         */
        if( !$helper->isIpAllowed() ) {
            $this->_forward( '404' );
            return;
        }

        /*
         * Storecode parameter check
         */
        if( $this->getRequest()->getParam( 'storecode' ) && !Mage::app()->getStore( $this->getRequest()->getParam( 'storecode' ) ) ) {
            $this->_forward( '404' );
            return;
        }


        $feed = Mage::getSingleton( 'flint_feefo/feed' );

        if( $this->getRequest()->getParam( 'start' ) ) {
            $feed->setFrom( date( 'Y-m-d 00:00:00', strtotime( $this->getRequest()->getParam( 'start' ) ) ) );
        } else {
            $feed->setFrom( date( 'Y-m-d 00:00:00' ) );
        }
        if( $this->getRequest()->getParam( 'end' ) ) {
            $feed->setTo( date( 'Y-m-d 23:59:59', strtotime( $this->getRequest()->getParam( 'end' ) ) ) );
        } else {
            $feed->setTo( date( 'Y-m-d 23:59:59' ) );
        }

        $feed->setPage( $this->getRequest()->getParam( 'page' ) );
        $feed->setStep( $this->getRequest()->getParam( 'step' ) );

        $feed->build();

        header( 'Content-Type: text/xml; charset=utf8' );
        $this->getResponse()->setHeader( 'Content-type', 'text/xml; charset=UTF-8' );
        $this->getResponse()->setBody( $feed->getDoc()->saveXML() );
    }

}
