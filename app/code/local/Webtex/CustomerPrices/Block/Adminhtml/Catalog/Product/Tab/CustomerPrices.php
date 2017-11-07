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
class Webtex_CustomerPrices_Block_Adminhtml_Catalog_Product_Tab_CustomerPrices extends Mage_Adminhtml_Block_Widget_Form 
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('webtex/customerprices/catalog/product/customerprices.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('customerprices_search_block',
            $this->getLayout()->createBlock('customerprices/Adminhtml_Catalog_Product_Tab_CustomerPrices_CustomerGrid')
        );

        $this->setChild('customerprices_box',
            $this->getLayout()->createBlock('customerprices/Adminhtml_Catalog_Product_Tab_CustomerPrices_CustomerPrices')
        );

        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => Mage::helper('catalog')->__('Add Customer'),
                'onclick'   => "customerPriceControl.addProductPrice();",
                'class'     => 'add'
            ));

        $button->setName('add_customer_price_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

    public function getCustomerpricesGridHtml()
    {
        return $this->getChildHtml('customerprices_search_block');
    }

    public function getCustomerpricesBoxHtml()
    {
        return $this->getChildHtml('customerprices_box');
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getWebsiteSelectHtml()
    {
        $html = '<select class="select required-entry" name="website_id" id="website-id">';

        $html .= '<option value="0">'.$this->__("All Websites").'</option>';

        foreach (Mage::app()->getWebsites() as $_websiteId => $_info){
            $html .= '<option value="'.$_websiteId.'">'.$this->htmlEscape($_info["name"]).'</option>';
        }
        $html .= '</select>';

        return $html;
    }
}
