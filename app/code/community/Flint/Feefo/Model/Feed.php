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
class Flint_Feefo_Model_Feed extends Varien_Object
{

    protected $doc;

    public function __construct() {
        $this->doc = new DOMDocument();
        $this->doc->encoding = 'utf-8';
    }

    public function build() {
        $helper = Mage::helper( 'flint_feefo/feeds' );

        $doc = $this->node( 'Items' );

        $orders = $helper->getOrdersCollection( $this->getFrom(), $this->getTo(), $this->getPage(), $this->getStep() );
        if( !is_null( $this->getPage() ) && !is_null( $this->getStep() ) ) {
            if( $this->getPage() > ceil( $orders->getSize() / $this->getStep() ) ) {
                $this->setPage( ceil( $orders->getSize() / $this->getStep() ) );
            }
            $doc->setAttribute( 'page', $this->getPage() );
            $doc->setAttribute( 'pages', ceil( $orders->getSize() / $this->getStep() ) );
        }

        $items = array();
        foreach( $orders as $order ) {
            foreach( $order->getAllVisibleItems() as $item ) {
                $items[] = $item->getProductId();
            }
        }

        $productsCollection = Mage::getModel( 'catalog/product' )->getCollection()
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('feefo_sku')
                ->addAttributeToFilter( 'entity_id', array( 'in' => $items ) );
        
        if($helper->getGeneralConfig()->getBusinessCategoryAttribute($helper->getFeedStore())){
            $productsCollection->addAttributeToSelect($helper->getGeneralConfig()->getBusinessCategoryAttribute($helper->getFeedStore()));
        }

        foreach( $orders as $order ) {
            foreach( $order->getAllVisibleItems() as $item ) {
                if($productsCollection->getItemById( $item->getProductId() )) {
                    $item->setProduct( $productsCollection->getItemById( $item->getProductId() ) );
                }
                if( !$item->getFeefoDisableFeeds() && $item->getProduct() && $this->getSku( $item ) && $item->getOrder()->getCustomerName() && $item->getOrder()->getCustomerEmail() ) {
                    $orderItem = $this->node( 'Item' );
                    $orderItem->appendChild( $this->node( 'Name', $item->getOrder()->getCustomerName() ) );
                    $orderItem->appendChild( $this->node( 'Email', $item->getOrder()->getCustomerEmail() ) );
                    $orderItem->appendChild( $this->node( 'Date', $helper->getXmlTimeFormat( $order->getCreatedAt() ) ) );
                    $orderItem->appendChild( $this->node( 'Feedback_Date', $helper->getFeedbackDay( $order, $item ) ) );
                    $orderItem->appendChild( $this->node( 'Description', $item->getName() ) );
                    $orderItem->appendChild( $this->node( 'Product_Search_Code', $this->getSku( $item ) ) );
                    $orderItem->appendChild( $this->node( 'Order_Ref', $item->getOrder()->getIncrementId() ) );
                    $orderItem->appendChild( $this->node( 'Customer_Ref', $item->getOrder()->getCustomerId() ) );
                    $orderItem->appendChild( $this->node( 'Product_Link', $this->getItemUrl( $item, $order->getStore()->getId() ) ) );
                    $orderItem->appendChild( $this->node( 'Logon', $helper->getGeneralConfig()->getLogon( $order->getStore()->getId() ) . '/' . $helper->getStoreCode( $order ) . $helper->getCategoryPath( $item ) ) );

                    $doc->appendChild( $orderItem );
                }
            }
        }

        $this->doc->appendChild( $doc );
    }

    protected function node( $name, $content = '' ) {
        $newElement = $this->doc->createElement( $name );
        if( $content instanceof DOMElement ) {
            $newElement->appendChild( $content );
        } elseif( strlen( $content ) ) {
            $newElement->appendChild( $this->doc->createTextNode( ( string ) $content ) );
        }
        return $newElement;
    }

    protected function getSku( $item ) {
        if($item->getProduct()->getFeefoSku()){
            return $item->getProduct()->getFeefoSku();
        }
        return $item->getProduct()->getSku();
    }

    protected function getItemUrl( $item, $storeId ) {
        if($item->getProduct()->getData('url')){
            $item->getProduct()->setUrl('');
        }
        return $item->getProduct()->getUrlInStore( array( '_store' => $storeId ) );
    }

    public function getDoc() {
        return $this->doc;
    }

}
