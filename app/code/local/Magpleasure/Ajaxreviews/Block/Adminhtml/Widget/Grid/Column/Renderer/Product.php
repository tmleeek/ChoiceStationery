<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Adminhtml_Widget_Grid_Column_Renderer_Product extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
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
            $product = Mage::getModel('catalog/product')->load($content);
            if ($product->getId()) {
                $link = Mage::getModel('adminhtml/url')->getUrl('adminhtml/catalog_product/edit', array('id' => $content));
                return '<a href="' . $link . '" target="_blank">' . $product->getName() . '</a>';
            }
        }
        return parent::render($row);
    }
}