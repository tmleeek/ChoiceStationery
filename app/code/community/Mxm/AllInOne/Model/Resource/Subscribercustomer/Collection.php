<?php

class Mxm_AllInOne_Model_Resource_Subscribercustomer_Collection extends Mage_Customer_Model_Resource_Customer_Collection
{
    protected function _getItemId(Varien_Object $item)
    {
        return $item->getSubscriberId();
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $select = $this->getSelect();
        $alias = 'subs';
        if (!array_key_exists($alias, $select->getPart(Zend_Db_Select::FROM))) {
            $subscriberTable = Mage::getSingleton('core/resource')->getTableName('newsletter/subscriber');
            $select->joinRight(
                    array($alias => $subscriberTable),
                    "`$alias`.`customer_id`=`e`.`entity_id`"
                );
        }
        return $this;
    }

    public function addOrderData()
    {
        $orderTable = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
        /* @var $select Varien_Db_Select */
        $select = Mage::getModel(
                'Varien_Db_Select',
                Mage::getSingleton('core/resource')->getConnection('core_read')
            );
        $select->from($orderTable, array(
                'cid' => 'customer_id',
                'total_spend' => 'sum(grand_total)',
                'total_items' => 'sum(total_qty_ordered)',
                'total_orders' => 'count(*)',
                'average_order_value' => 'avg(grand_total)',
                'last_order_date' => 'max(created_at)',
                'last_order_id' => 'max(entity_id)'
            ))
            ->group('customer_id')
        ;
        $this->getSelect()->joinLeft(
            array('order' => new Zend_Db_Expr("($select)")),
            '`order`.`cid` = `e`.`entity_id`'
        );
        return $this;
    }

    public function addRecentProductData()
    {
        $orderTable     = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
        $orderItemTable = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
        $subselect = <<<SQL
SELECT customer_id as cid, GROUP_CONCAT(product_id ORDER BY iid DESC)as recent_products
FROM
(
SELECT o.customer_id, oi.product_id, MAX(oi.item_id) AS iid
FROM $orderTable AS o
INNER JOIN $orderItemTable AS oi ON oi.order_id = o.entity_id
WHERE oi.parent_item_id IS NULL
GROUP BY o.customer_id, oi.product_id
) AS dedupe
GROUP BY customer_id
SQL;
        $this->getSelect()->joinLeft(
            array('recent' => new Zend_Db_Expr("($subselect)")),
            '`recent`.`cid` = `e`.`entity_id`'
        );
        return $this;
    }
}
