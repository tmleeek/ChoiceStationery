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


class AW_Advancedreports_Model_Mysql4_Collection_Usergroups extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{
    /**
     * Add groups
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Usergroups
     */
    public function addCustomerGroups($isAllStores = false)
    {
        if ($isAllStores) {
            $currencyRate = "main_table.store_to_base_rate";
        } else {
            $currencyRate = new Zend_Db_Expr("'1'");
        }

        $itemTable = Mage::helper('advancedreports/sql')->getTable('sales_flat_order_item');
        $customerEntityTable = Mage::helper('advancedreports/sql')->getTable('customer_entity');
        $customerGroupTable = Mage::helper('advancedreports/sql')->getTable('customer_group');

        $this->getSelect()
            ->join(
                array('item' => $itemTable),
                "main_table.entity_id = item.order_id AND item.parent_item_id IS NULL",
                array(
                    'sum_qty'   => 'SUM(item.qty_ordered)',
                    'sum_total' => "SUM(item.base_row_total * $currencyRate) ",
                    'name'      => 'name',
                    'sku'       => 'sku',
                )
            )
            ->joinLeft(
                array('cust' => $customerEntityTable),
                "main_table.customer_id = cust.entity_id AND main_table.customer_id IS NOT NULL",
                array()
            )
            ->joinLeft(
                array('grp' => $customerGroupTable),
                "grp.customer_group_id = IFNULL(cust.group_id, '0')",
                array(
                    'group_name' => 'customer_group_code',
                    'group_id' => 'customer_group_id',
                )
            )
            ->group('grp.customer_group_id');

        return $this;
    }
}