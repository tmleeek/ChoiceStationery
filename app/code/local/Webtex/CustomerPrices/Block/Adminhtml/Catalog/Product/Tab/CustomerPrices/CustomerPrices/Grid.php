<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_CustomerPrices
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtex.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Customer Prices extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerPrices
 * @author     Webtex Dev Team <dev@webtex.com>
 */
class Webtex_CustomerPrices_Block_Adminhtml_Catalog_Product_Tab_CustomerPrices_CustomerPrices_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_product;

    public function __construct()
    {
        parent::__construct();
        $this->setId('customerPricesProductGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('customer_id');
        $this->setRowClickCallback('customerPriceControl.selectPrice.bind(customerPriceControl)');
        $this->_product = Mage::registry('current_product');
    }


    protected function _prepareCollection()
    {

        if($this->_product->getId()) {
            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
            $product = Mage::getModel('catalog/product');
            $collection = Mage::getModel('customerprices/prices')->getCollection()->addProductFilter($this->_product->getId(), false)->addFieldToFilter('qty',array('gt' => 0));
            $this->setCollection($collection);
            parent::_prepareCollection();
        }

    }


    protected function _prepareColumns()
    {
        $this->addColumn('customer_id', array(
            'header'    => Mage::helper('catalog')->__('Customer ID'),
            'width'     => '50px',
            'index'     => 'customer_id',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Customer Name'),
            'width'     => '250px',
            'index'     => 'name',
            'renderer'  => 'customerprices/Adminhtml_Catalog_Product_Tab_CustomerPrices_Customernamerenderer',
            'filter'    => false,
            'sortable'      => false,
        ));

        $this->addColumn('customer_email', array(
            'header'    => Mage::helper('catalog')->__('Customer Email'),
            'index'     => 'customer_email',
            'width'     => '100px',
        ));

        $this->addColumn('price', array(
            'header'    => Mage::helper('catalog')->__('Price'),
            'index'     => 'price',
            'width'     => '80px',
            'align'     => 'right',
        ));

        $this->addColumn('special_price', array(
            'header'    => Mage::helper('catalog')->__('Special Price'),
            'index'     => 'special_price',
            'width'     => '80px',
            'align'     => 'right',
        ));

        $this->addColumn('qty', array(
            'header'    => Mage::helper('catalog')->__('Qty'),
            'index'     => 'qty',
            'type'      => 'number',
            'width'     => '80px',
            'align'     => 'center',
            'renderer'  => 'customerprices/adminhtml_customer_edit_tab_customerprices_qtyrenderer',
        ));

        if(Mage::getStoreConfig('catalog/price/scope')) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('catalog')->__('Website'),
                'index'     => 'store_id',
                'width'     => '80px',
                'align'     => 'center',
                'renderer'  => 'customerprices/adminhtml_customer_edit_tab_customerprices_websiterenderer',
            ));
        }

        $this->addColumn('action', array(
            'header'    => Mage::helper('catalog')->__('Action'),
            'width'     => '50px',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('customer')->__('Delete'),
                    'url'     => $this->getUrl('customerprices/catalog_product/deleterow'),
                    'field'   => 'entity_id',
                )
            ),
            'renderer'  => 'customerprices/adminhtml_customer_edit_tab_customerprices_actionrenderer',
            'sortable'  => false,
            'filter'    => false,
            'align'     => 'center',
        ));

        return parent::_prepareColumns();
    }


    public function getWebsites()
    {
        if (!is_null($this->_websites)) {
            return $this->_websites;
        }

        $this->_websites = array(
            0   => array(
                'name'      => Mage::helper('catalog')->__('All Websites'),
                'currency'  => Mage::app()->getBaseCurrencyCode()
            )
        );

        $websites           = Mage::app()->getWebsites(false);
        foreach ($websites as $website) {
            $this->_websites[$website->getId()] = array(
                'name'      => $website->getName(),
                'currency'  => $website->getBaseCurrencyCode()
            );
        }
        
        return $this->_websites;
    }

    public function getDefaultWebsite()
    {
        return Mage::app()->getStore($this->_customer->getStoreId())->getWebsiteId();
    }

    public function allowChangeWebsite()
    {
        return false;
    }
    
    public function getRowUrl($row)
    {
        return $row->getId();
    }

    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/customerprices_catalog_product/customerpricesgrid', array('_current' => true));
    }
}
