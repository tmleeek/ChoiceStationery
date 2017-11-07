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

class Webtex_CustomerPrices_Block_Adminhtml_Customer_Edit_Tab_Customerprices_Productgrid extends Mage_Adminhtml_Block_Widget
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('webtex/customerprices/customer/edit/customerpricessearch.phtml');
        $this->setId('customerPricesCustomerGrid');
        $this->setIsCollapsed(true);
    }
    
    protected function _prepareLayout()
    {
        $this->setChild('customerprices_customers_grid',
            $this->getLayout()->createBlock('customerprices/Adminhtml_Customer_Edit_Tab_Customerprices_Productgrid_Grid')
        );
        return parent::_prepareLayout();
    }
    
    public function getHeaderText()
    {
        return Mage::helper('customerprices')->__('Please Select Product');
    }

    public function getHeaderCssClass()
    {
        return 'head-catalog-product';
    }

}
