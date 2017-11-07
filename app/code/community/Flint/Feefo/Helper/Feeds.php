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
class Flint_Feefo_Helper_Feeds extends Flint_Feefo_Helper_Data
{

    public $ordersStatus;

    public function getOrdersCollection( $from = false, $to = false, $page = null, $step = null )
    {
        $ordersId = array();
        foreach( $this->getStores() as $store ) {
            $ordersStatus = Mage::getResourceModel( 'sales/order_status_history_collection' )
                    ->addFieldToSelect( 'entity_id' )
                    ->distinct();
            $ordersStatus->getSelect()->group( array( 'main_table.parent_id', 'main_table.status' ) );

            $entity_ids = array();
            foreach( $ordersStatus as $orderStatus ) {
                $entity_ids[] = $orderStatus->getEntityId();
            }

            $ordersStatus = Mage::getResourceModel( 'sales/order_status_history_collection' )
                    ->addFieldToSelect( '*' )
                    ->addFieldToFilter( 'main_table.entity_id', array( 'in' => $entity_ids ) )
                    ->addFieldToFilter( 'main_table.status', array( 'in' => explode( ',', $this->getGeneralConfig()->getQueryDate( $store->getId() ) ) ) )
                    ->addFieldToFilter( 'main_table.created_at', array( 'from' => $from, 'to' => $to ) )
                    ->setOrder( 'entity_id', 'asc' )
                    ->join( array( 'order' => 'sales/order' ), 'main_table.parent_id = order.entity_id', array( 'order.store_id' ) )
                    ->addFieldToFilter( 'order.store_id', array( 'eq' => $store->getId() ) );
            $ordersStatus->load();

            foreach( $ordersStatus as $orderStatus ) {
                if( !isset( $ordersId[ $orderStatus->getParentId() ] ) ) {
                    $this->ordersStatus[ $orderStatus->getParentId() ] = $orderStatus;
                    $ordersId[ $orderStatus->getParentId() ] = $orderStatus->getParentId();
                }
            }
        }
        $orders = Mage::getResourceModel( 'sales/order_collection' )
                ->addFieldToSelect( '*' )
                ->addFieldToFilter( 'entity_id', array( 'in' => $ordersId ) )
                ->setOrder( 'created_at', 'asc' );

        if( $store = $this->getFeedStore() ) {
            $orders->addFieldToFilter( 'store_id', $store->getId() );
        }

        if( !is_null( $page ) && !is_null( $step ) ) {
            $orders->setPage( $page, $step );
        }
        return $orders;
    }

    public function getFeedbackDay( $order, $item )
    {
        if( !$this->getGeneralConfig()->getQueryDateOffset( $order->getStore()->getId() ) )
            return '';

        $queryOffset = $this->getGeneralConfig()->getQueryDateOffset( $order->getStore()->getId() );
        if( $orderStatus = $this->ordersStatus[ $order->getEntityId() ] ) {
            return date( "d-m-Y", strtotime( $orderStatus->getCreatedAt() ) + $queryOffset * 24 * 60 * 60 );
        }
        return '';
    }

    public function getXmlTimeFormat( $date )
    {
        return date( "d-m-Y", strtotime( $date ) );
    }

    public function getStoreCode( $order )
    {
        if( !$this->getGeneralConfig()->getIncludeStoreCode( $order->getStore()->getId() ) )
            return null;

        return $order->getStore()->getCode() . '/';
    }

    public function getStores()
    {
        //store
        if( $store = $this->getFeedStore() ) {
            return array( $store );
        }
        return Mage::app()->getStores();
    }

    public function getCategoryPath( $item )
    {
        switch( $this->getGeneralConfig()->getBusinessCategory( $item->getOrder()->getStore()->getId() ) ) {
            case 'business':
                if( $item->getFeefoBusinessCategory() ) {
                    return $item->getFeefoBusinessCategory();
                }
                break;
            case 'category':
                $path = array();
                foreach( $item->getProduct()->getCategoryCollection() as $category ) {
                    foreach( array_reverse( $category->getParentCategories() ) as $parentCategory ) {
                        $path[] = $parentCategory->getName();
                    }
                    break;
                }
                return implode( '/', $path );
                break;
            case 'attribute' && $this->getGeneralConfig()->getBusinessCategoryAttribute( $item->getOrder()->getStore()->getId() ):
                if( $item->getProduct()->getAttributeText( $this->getGeneralConfig()->getBusinessCategoryAttribute( $item->getOrder()->getStore()->getId() ) ) ) {
                    return $item->getProduct()->getAttributeText( $this->getGeneralConfig()->getBusinessCategoryAttribute( $item->getOrder()->getStore()->getId() ) );
                } else {
                    return $item->getProduct()->getData( $this->getGeneralConfig()->getBusinessCategoryAttribute( $item->getOrder()->getStore()->getId() ) );
                }
                break;
        }

        return null;
    }

    public function isIpAllowed()
    {
        $allow = true;

        $allowedIps = $this->getGeneralConfig()->getFirewall( null );
        if( !empty( $allowedIps ) ) {
            $allowedIps = preg_split( '#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY );
            if( array_search( Mage::helper( 'core/http' )->getRemoteAddr(), $allowedIps ) === false ) {
                $allow = false;
            }
        }

        return $allow;
    }

    public function getFeedStore()
    {
        if( $this->_getRequest()->getParam( 'storecode' ) )
            return Mage::app()->getStore( $this->_getRequest()->getParam( 'storecode' ) );
        return null;
    }

    public function getGeneralConfig()
    {
        return Mage::getSingleton( 'flint_feefo/config_general' );
    }

}
