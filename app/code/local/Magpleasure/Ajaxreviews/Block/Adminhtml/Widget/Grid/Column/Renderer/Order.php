<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Adminhtml_Widget_Grid_Column_Renderer_Order extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $content = $this->_getValue($row);
        if ($content) {
            $order = Mage::getModel('sales/order')->load($content);
            if ($order->getId()) {
                $link = Mage::getModel('adminhtml/url')->getUrl('adminhtml/sales_order/view', array('order_id' => $content));
                return '<a href="' . $link . '" target="_blank">' . $order->getIncrementId() . '</a>';
            }
        }
        return parent::render($row);
    }
}