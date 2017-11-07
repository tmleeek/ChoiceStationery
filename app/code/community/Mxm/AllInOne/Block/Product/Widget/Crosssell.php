<?php


class Mxm_AllInOne_Block_Product_Widget_Crosssell
    extends Mage_Catalog_Block_Product_List_Crosssell
    implements Mage_Widget_Block_Interface
{
    protected $_items = null;

    protected $_itemIndex = 0;

    protected function _construct()
    {
        $this->setArea('frontend')
            ->setTemplate('mxmallinone/product/widget/related.phtml');

        return parent::_construct();
    }

    protected function _prepareData()
    {
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

    public function getItemCollection()
    {
        return $this->_itemCollection;
    }

    public function getItems()
    {
        if (is_null($this->_items)) {
            $this->_items = $this->getItemCollection()->getItems();
        }
        return $this->_items;
    }

    public function getRowCount()
    {
        $total = min(count($this->getItems()), $this->getProducts());
        return ceil($total/$this->getColumnCount());
    }

    public function getColumnCount()
    {
        return $this->getColumns();
    }

    public function resetItemsIterator()
    {
        $this->getItems();
        reset($this->_items);
        $this->_itemIndex = 0;
    }

    public function getIterableItem()
    {
        if ($this->_itemIndex++ === $this->getProducts()) {
            return null;
        }
        $item = current($this->_items);
        next($this->_items);
        return $item;
    }

}
