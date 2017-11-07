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

class Webtex_CustomerPrices_Block_Adminhtml_Customer_Edit_Tab_Customerprices_Productgrid_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('customerPricesCustomerSearchGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
        $this->setRowClickCallback('customerPriceControl.selectProduct.bind(customerPriceControl)');
    }


    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('price');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('customer')->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('customer')->__('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('sku', array(
            'header'    => Mage::helper('customer')->__('SKU'),
            'width'     => '150',
            'index'     => 'sku'
        ));
        $this->addColumn('price', array(
            'header'    => Mage::helper('customer')->__('Price'),
            'width'     => '50',
            'index'     => 'price'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $row->getId();
    }

    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/customerprices_customer/productsgrid', array('_current' => true));
    }
}
