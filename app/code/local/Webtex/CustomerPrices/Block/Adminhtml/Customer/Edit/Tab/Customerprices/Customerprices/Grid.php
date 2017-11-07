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
class Webtex_CustomerPrices_Block_Adminhtml_Customer_Edit_Tab_Customerprices_Customerprices_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_customer;
    protected $_products;
    protected $_websites;

    public function __construct()
    {
        parent::__construct();
        $this->setId('customerPricesProductGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('sku');
        $this->_customer = Mage::registry('current_customer');
        $this->setRowClickCallback('customerPriceControl.selectPrice.bind(customerPriceControl)');
    }


    public function getProduct()
    {
        return $this->_products;
    }

    protected function _prepareCollection()
    {
        if($this->_customer->getId()) {
            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
            $product = Mage::getModel('catalog/product');
            $collection = Mage::getModel('customerprices/prices')->getCollection()->addCustomerFilter($this->_customer->getId())->addFieldToFilter('qty',array('gt' => 0));
            $collection->getSelect()
                ->joinLeft(
                    array(
                        'products' => $tablePrefix . 'catalog_product_entity'
                    ),
                     'main_table.product_id = products.entity_id',
                    array(
                        'sku'  => 'sku',
                    )
                );
            $collection->getSelect()
                ->joinLeft(
                    array('products_attr' => $tablePrefix . 'catalog_product_entity_varchar'),
                    'main_table.product_id = products_attr.entity_id 
                     and main_table.store_id = products_attr.store_id
                     and attribute_id in (select attribute_id from '.$tablePrefix.'eav_attribute where attribute_code like "name")',
                    array('name' => 'value')
                );
            $this->setCollection($collection);
        }

        return parent::_prepareCollection();
    }


    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Product Name'),
            'width'     => '250px',
            'index'     => 'name',
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'index'     => 'sku',
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
                    'url'     => $this->getUrl('customerprices/customer/deleterow'),
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
        return $this->getUrl('adminhtml/customerprices_customer/customerpricesgrid', array('_current' => true));
    }
}
