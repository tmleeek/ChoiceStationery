<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_CustomerOrders extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

  public function render(Varien_Object $row) {

    $html = '';

    $customerEmail = $row->getemail();

    if ($customerEmail) {

      $collection = Mage::getModel('sales/order')
              ->getCollection()
              ->addAttributeToSelect('*')
              ->addAttributeToFilter('customer_email', $customerEmail);

      $nb = count($collection);

      if ($nb > 0) {

        foreach ($collection as $order) {

          if($order){
            $orderId = $order->getId();
            $orderDisplayId = $order->getincrement_id();

            if($orderId && $orderDisplayId){

              $urlInfo = array('url' => 'adminhtml/sales_order/view', 'param' => array('order_id' => $orderId));
              $url = $this->getUrl($urlInfo['url'], $urlInfo['param']);

              $additionnalData = '';
              
              $marketPlaceOrderId = $order->getmarketplace_order_id();
              if($marketPlaceOrderId)
              {
                $additionnalData .= ' - '.$marketPlaceOrderId;
              }

              $orderDate = $order->getcreated_at();
              if($orderDate){
                $orderDate = Mage::helper('core')->formatDate($orderDate, 'short', true);
                $additionnalData .= ' - <i>'.$orderDate.'</i>';
              }

              $orderGrandTotal = $order->getgrand_total();
              if($orderGrandTotal){

                $currencySymbol = $order->getOrderCurrencyCode();
                if(!$currencySymbol){
                  $currency = $order->getCurrency();
                  if($currency) {
                    $currencySymbol = $currency->getCode();
                  }else{
                    $currencySymbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
                  }
                }

                $orderGrandTotal = round($orderGrandTotal, 2);
                $additionnalData .= ' - ('.$orderGrandTotal.' '.$currencySymbol.')';
              }

              $html .= '<p><a href="' . $url . '" target="_blank">#' . $orderDisplayId . '</a>'.$additionnalData.'</p>';
            }
          }
          
        }

        return $html;
      }
    }
  }
}
