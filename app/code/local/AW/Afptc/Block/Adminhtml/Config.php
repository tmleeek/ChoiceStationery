<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Afptc
 * @version    1.1.12
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Afptc_Block_Adminhtml_Config extends Mage_Core_Block_Template {

    protected function _toHtml()
    {
        if ($this->helper('awafptc')->isAcpEnabled()) {
            return '';
        } else {
            return parent::_toHtml();
        }

    }

    public function getACPNote() {
        $extensionUrl ='http://ecommerce.aheadworks.com/magento-extensions/ajax-cart-pro.html';
        $html = '<span>';
        $html .= '<a href="' . $extensionUrl . '" target="_blank">Ajax Cart Pro</a> ';
        $html .= $this->__('extension by aheadWorks is not installed.');
        $html .= '</span>';
        return $html;
    }


}