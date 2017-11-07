<?php
class LucidPath_SalesRep_Model_Adminhtml_Observer {

  public function addColumn(Mage_Adminhtml_Block_Sales_Order_Grid $block) {
    if ($collection = $block->getCollection ()) {
      $collection->getSelect()->joinLeft(array('salesrep' => $collection->getTable('salesrep/salesrep')), 'salesrep.order_id=entity_id');

      if (!Mage::getSingleton('admin/session')->isAllowed('system/config')) {
        // don't have full permissions - show own orders
        // $collection->addAttributeToFilter('salesrep.admin_id', array('eq' => Mage::getSingleton('admin/session')->getUser()->getId()));
      }

      if (Mage::getStoreConfig('salesrep/order_grid/commission_payment_status')) {
        $block->addColumnAfter('commission_status',
                               array(
                                     'header' => Mage::helper('salesrep')->__('Comm. Status'),
                                     'index' => 'commission_status',
                                     'type'  => 'options',
                                     'align' => 'center',
                                     'width' => '10px',
                                     'options' => Mage::helper('salesrep')->getStatusListFilter(),
                                     'renderer' => 'LucidPath_SalesRep_Block_Adminhtml_Order_Grid_Renderer_PaymentStatus',
                                     'options' => Mage::helper('salesrep')->getCommissionStatusList()),
                               'status');
      }

      if (Mage::getStoreConfig('salesrep/order_grid/commission_amount')) {
        $block->addColumnAfter('commission_earned',
                               array(
                                     'header' => Mage::helper('salesrep')->__('Comm. Amount'),
                                     'index' => 'commission_earned',
                                     'align' => 'center',
                                     'width' => '10px',
                                     'renderer' => 'LucidPath_SalesRep_Block_Adminhtml_Order_Grid_Renderer_Amount'),
                               'status');
      }

      if (Mage::getStoreConfig('salesrep/order_grid/commission_earner')) {
        $block->addColumnAfter('admin_name',
                               array(
                                     'header' => Mage::helper('salesrep')->__('Comm. Earner'),
                                     'index' => 'admin_name',
                                     'align' => 'center',
                                     'width' => '10px',
                                     'renderer' => 'LucidPath_SalesRep_Block_Adminhtml_Order_Grid_Renderer_Earner'),
                               'status');
      }

      $block->sortColumnsByOrder();
    }

    $filter = $block->getParam($block->getVarNameFilter(), null);

    if (is_string($filter)) {
      $filter = $block->helper('adminhtml')->prepareFilterString($filter);
    } else if ($filter && is_array ($filter)) {
    } else if (0 !== sizeof($block->_defaultFilter)) {
      $filter = $block->_defaultFilter;
    }

    $params = array('admin_name', 'commission_earned', 'commission_status');

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

  public function eav_collection_abstract_load_before(Varien_Event_Observer $observer) {
    $collection = $observer->getCollection();
    if (!isset ($collection)) return;

    if (is_a($collection, 'Mage_Customer_Model_Resource_Customer_Collection')) {
      if (($block = Mage::app()->getLayout()->getBlock('grid')) != false) {
        if (!Mage::getSingleton('admin/session')->isAllowed('system/config')) {
          // don't have full permissions - show own customers
          try {
            $collection->getSelect()->where('e.salesrep_admin_id = ?', Mage::getSingleton('admin/session')->getUser()->getId());
          } catch (Exception $e) {}
        }
      }
    }
  }

  public function onEavLoadBefore(Varien_Event_Observer $observer) {
    $collection = $observer->getCollection();
    if (!isset ($collection)) return;

    if (is_a($collection, 'Mage_Sales_Model_Resource_Order_Grid_Collection')) {
      if (($block = Mage::app()->getLayout()->getBlock('sales_order.grid')) != false) {
        $this->addColumn($block);
      }
    }
  }

  public function customerSaveAfter(Varien_Event_Observer $observer) {
    $customer = $observer->getEvent()->getCustomer();

    try {
      $admin_id = Mage::app()->getRequest()->getPost('salesrep_admin');

      $write = Mage::getSingleton('core/resource')->getConnection('core_write');
      $table = Mage::getSingleton('core/resource')->getTableName('customer/entity');

      $write->query("UPDATE {$table} SET salesrep_admin_id = '". $admin_id ."' WHERE entity_id = ". $customer->getId() .";");
    } catch (Exception $e) {
      echo $e->getMessage();
      exit;

      Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
    }
  }
}
?>