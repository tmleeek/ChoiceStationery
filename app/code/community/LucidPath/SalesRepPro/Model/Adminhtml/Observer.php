<?php
class LucidPath_SalesRepPro_Model_Adminhtml_Observer {

  public function addColumn(Mage_Adminhtml_Block_Sales_Order_Grid $block) {
    if ($collection = $block->getCollection ()) {
      $joined_tables = array_keys($collection->getSelect()->getPart('from'));

      if (!in_array('salesrep', $joined_tables)) {
       $collection->getSelect()->joinLeft(array('salesrep' => $collection->getTable('salesrep/salesrep')), 'salesrep.order_id=main_table.entity_id');

        if (Mage::getStoreConfig('salesrep/order_grid/commission_payment_status')) {
          $block->addColumnAfter('rep_commission_status',
                                 array(
                                       'header' => Mage::helper('salesrep')->__('Comm. Status'),
                                       'index' => 'rep_commission_status',
                                       'type'  => 'options',
                                       'align' => 'center',
                                       'width' => '10px',
                                       'options' => Mage::helper('salesrep')->getStatusListFilter(),
                                       'renderer' => 'LucidPath_SalesRepPro_Block_Adminhtml_Order_Grid_Renderer_PaymentStatus',
                                       'options' => Mage::helper('salesrep')->getCommissionStatusList()),
                                 'status');
        }

        if (Mage::getStoreConfig('salesrep/order_grid/commission_amount')) {
          $block->addColumnAfter('rep_commission_earned',
                                 array(
                                       'header' => Mage::helper('salesrep')->__('Comm. Amount'),
                                       'index' => 'rep_commission_earned',
                                       'align' => 'center',
                                       'width' => '10px',
                                       'renderer' => 'LucidPath_SalesRepPro_Block_Adminhtml_Order_Grid_Renderer_Amount'),
                                 'status');
        }

        if (Mage::getStoreConfig('salesrep/order_grid/commission_earner')) {
          $block->addColumnAfter('rep_name',
                                 array(
                                       'header' => Mage::helper('salesrep')->__('Comm. Earner'),
                                       'index' => 'rep_name',
                                       'align' => 'center',
                                       'width' => '10px',
                                       'renderer' => 'LucidPath_SalesRepPro_Block_Adminhtml_Order_Grid_Renderer_Earner'),
                                 'status');
        }

        $block->sortColumnsByOrder();
      }
     }

    $filter = $block->getParam($block->getVarNameFilter(), null);

    if (is_string($filter)) {
      $filter = $block->helper('adminhtml')->prepareFilterString($filter);
    } else if ($filter && is_array ($filter)) {
    } else if (0 !== sizeof($block->_defaultFilter)) {
      $filter = $block->_defaultFilter;
    }

    $params = array('rep_name', 'rep_commission_earned', 'rep_commission_status');

    foreach ($params as $param) {
      $column = $block->getColumn($param);

      if (isset($filter[$param]) && (!empty($filter[$param]) || strlen($filter[$param]) > 0) && $column->getFilter()) {
        $column->getFilter()->setValue($filter[$param]);

        $collection = $block->getCollection();

        if ($collection) {
          $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();

          if ($column->getFilterConditionCallback()) {
            call_user_func($column->getFilterConditionCallback(), $collection, $column);
          } else {
            $cond = $column->getFilter()->getCondition();

            if ($field && isset ($cond)) {
              $collection->addFieldToFilter('salesrep.'. $param, $cond);
            }
          }
        }
      }
    }
  }

  public function onEavLoadBefore(Varien_Event_Observer $observer) {
    if (!Mage::helper('salesrep')->isModuleEnabled()) {
      return true;
    }

    $collection = $observer->getCollection();
    if (!isset ($collection)) return;

    if ($collection instanceof Mage_Sales_Model_Resource_Order_Grid_Collection) {
      $layout = Mage::app()->getLayout();

      if (($block = Mage::app()->getLayout()->getBlock('sales_order.grid')) != false) {
        $this->addColumn($block);
      }
    }
  }
}
?>
