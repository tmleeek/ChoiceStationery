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
 * @package    AW_Advancedreports
 * @version    2.7.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Advancedreports_Model_Mysql4_Collection_Standard_Sales
    extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{
    /**
     * Reinitialize select
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Product
     */
    public function reInitSelect()
    {
        $orderTable = Mage::helper('advancedreports/sql')->getTable('sales_flat_order');

        $this->getSelect()->reset();
        $this->getSelect()->from(array('main_table' => $orderTable), array());
        return $this;
    }

    /**
     * Add order columns
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Standard_Sales
     */
    public function addSumColumns($isAllStores = false)
    {
        if ($isAllStores) {
            $currencyRate = "main_table.store_to_base_rate";
        } else {
            $currencyRate = new Zend_Db_Expr("'1'");

        }

        $this->getSelect()->columns(
            array(
                'orders'      => "COUNT(main_table.entity_id)", # Just because it's unique
                'subtotal'    => "SUM(main_table.base_subtotal * $currencyRate)",
                'tax'         => "SUM(main_table.base_tax_amount * $currencyRate)",
                'discount'    => "SUM(main_table.base_discount_amount * $currencyRate)",
                'shipping'    => "SUM(main_table.base_shipping_amount * $currencyRate)",
                'total'       => "(SUM(main_table.base_subtotal * $currencyRate)
                                  + SUM(main_table.base_tax_amount * $currencyRate)
                                  + SUM(main_table.base_discount_amount * $currencyRate)
                                  + SUM(main_table.base_shipping_amount * $currencyRate))",
                'invoiced'    => "SUM(main_table.base_total_invoiced * $currencyRate)",
                'refunded'    => "SUM(main_table.base_total_refunded * $currencyRate)",
                'items_count' => "SUM(main_table.total_qty_ordered)",
                'int_1'       => "ROUND(1)",
            )
        );
        return $this;
    }

    public function addItems()
    {
        $itemTable = Mage::helper('advancedreports/sql')->getTable('sales_flat_order_item');

        $this->getSelect()
            ->join(
                array('item' => $itemTable),
                "main_table.entity_id = item.order_id AND item.parent_item_id IS NULL",
                array(
                    'items_count' => 'SUM(item.qty_ordered)',
                )
            );
        return $this;
    }

    /**
     * Group by Entity_Id
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Standard_Sales
     */
    public function addGroupByEntityId()
    {
        $this->getSelect()->group("main_table.entity_id");
        return $this;
    }

    /**
     * Group by INT_1
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Standard_Sales
     */
    public function addGroupByIntOne()
    {
        $this->getSelect()->group('int_1');
        return $this;
    }
}