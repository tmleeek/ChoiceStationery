<?php


class Mxm_AllInOne_Block_Product_Widget_Upsell
    extends Mage_Catalog_Block_Product_List_Upsell
    implements Mage_Widget_Block_Interface
{

    protected function _construct()
    {
        $this->setArea('frontend')
            ->setTemplate('mxmallinone/product/widget/related.phtml');

        return parent::_construct();
    }

    protected function _prepareData()
    {
        $this->setColumnCount($this->getColumns());
        $this->setItemLimit('upsell', $this->getProducts());

        try {
            $order = $this->getOrder();
            if (!$order instanceof Mage_Sales_Model_Order) {
                $order = Mage::helper('mxmallinone/widget')->getSubscriberOrder($this->getSubscriber());
            }
            $product = Mage::helper('mxmallinone/widget')->getProductFromOrder($order);
        } catch (Exception $e) {
            return;
        }

        Mage::unregister('product');
        Mage::register('product', $product);

        return parent::_prepareData();
    }
}
