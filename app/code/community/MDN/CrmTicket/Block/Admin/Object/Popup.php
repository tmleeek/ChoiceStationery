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
class MDN_CrmTicket_Block_Admin_Object_Popup extends Mage_Adminhtml_Block_Widget_Form {
    
    /**
     * Load object
     */
    public function getObject()
    {
        $class = Mage::getModel('CrmTicket/Customer_Object')->getClassByType($this->getObjectType());
        return $class->loadObject($this->getObjectId());
    }

    /**
     * get comment history
     *
     * @param type $order
     * @return type
     */
    public function getFullHistory($order)
    {
        $history = array();
        foreach ($order->getAllStatusHistory() as $orderComment){
            $history[] = $this->_prepareHistoryItem(
                $orderComment->getStatusLabel(),
                $orderComment->getIsCustomerNotified(),
                $orderComment->getCreatedAtDate(),
                $orderComment->getComment()
            );
        }

        foreach ($order->getCreditmemosCollection() as $_memo){
            $history[] = $this->_prepareHistoryItem(
                $this->__('Credit memo #%s created', $_memo->getIncrementId()),
                $_memo->getEmailSent(),
                $_memo->getCreatedAtDate()
            );

            foreach ($_memo->getCommentsCollection() as $_comment){
                $history[] = $this->_prepareHistoryItem(
                    $this->__('Credit memo #%s comment added', $_memo->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $_comment->getCreatedAtDate(),
                    $_comment->getComment()
                );
            }
        }

        foreach ($order->getShipmentsCollection() as $_shipment){
            $history[] = $this->_prepareHistoryItem(
                $this->__('Shipment #%s created', $_shipment->getIncrementId()),
                $_shipment->getEmailSent(),
                $_shipment->getCreatedAtDate()
            );

            foreach ($_shipment->getCommentsCollection() as $_comment){
                $history[] = $this->_prepareHistoryItem(
                    $this->__('Shipment #%s comment added', $_shipment->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $_comment->getCreatedAtDate(),
                    $_comment->getComment()
                );
            }
        }

        foreach ($order->getInvoiceCollection() as $_invoice){
            $history[] = $this->_prepareHistoryItem(
                $this->__('Invoice #%s created', $_invoice->getIncrementId()),
                $_invoice->getEmailSent(),
                $_invoice->getCreatedAtDate()
            );

            foreach ($_invoice->getCommentsCollection() as $_comment){
                $history[] = $this->_prepareHistoryItem(
                    $this->__('Invoice #%s comment added', $_invoice->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $_comment->getCreatedAtDate(),
                    $_comment->getComment()
                );
            }
        }

        foreach ($order->getTracksCollection() as $_track){
            $history[] = $this->_prepareHistoryItem(
                $this->__('Tracking number %s for %s assigned', $_track->getNumber(), $_track->getTitle()),
                false,
                $_track->getCreatedAtDate()
            );
        }

        try
        {
          usort($history, array(__CLASS__, "_sortHistoryByTimestamp"));
        }catch (Exception $ex){
          //ignore
        }

        return $history;
    }

    /**
     * Map history items as array
     *
     * @param string $label
     * @param bool $notified
     * @param Zend_Date $created
     * @param string $comment
     * @return array
     */
    protected function _prepareHistoryItem($label, $notified, $created, $comment = '')
    {
        return array(
            'title'      => $label,
            'notified'   => $notified,
            'comment'    => $comment,
            'created_at' => $created
        );
    }


    /**
     * Comparison For Sorting History By Timestamp
     *
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    private static function _sortHistoryByTimestamp($a, $b)
    {
        $createdAtA = $a['created_at'];
        $createdAtB = $b['created_at'];

        /** @var $createdAta Zend_Date */
        if ($createdAtA->getTimestamp() == $createdAtB->getTimestamp()) {
            return 0;
        }
        return ($createdAtA->getTimestamp() < $createdAtB->getTimestamp()) ? -1 : 1;
    }


    public function getOrderLink($title, $orderId){
      $urlInfo = Mage::getModel('CrmTicket/Customer_Object_Order')->getObjectAdminLink($orderId);
      $url = $this->getUrl($urlInfo['url'], $urlInfo['param']);
      return '<a href="' . $url . '" target="_blank">' . $title . '</a>';
    }

    
    public function getShipmentPDF($title, $id){
      $urlInfo =  array('url' => 'adminhtml/sales_order_shipment/print', 'param' => array('invoice_id' => $id));
      $url = $this->getUrl($urlInfo['url'], $urlInfo['param']);
      return '<a href="' . $url . '" target="_blank">' . $title . '</a>';
    }

    public function getInvoicePDF($title, $id){
      $urlInfo =  array('url' => 'adminhtml/sales_order_invoice/print', 'param' => array('invoice_id' => $id));
      $url = $this->getUrl($urlInfo['url'], $urlInfo['param']);
      return '<a href="' . $url . '" target="_blank">' . $title . '</a>';
    }

    public function getCreditMemoPDF($title, $id){
      $urlInfo =  array('url' => 'adminhtml/sales_order_creditmemo/print', 'param' => array('creditmemo_id' => $id));
      $url = $this->getUrl($urlInfo['url'], $urlInfo['param']);
      return '<a href="' . $url . '" target="_blank">' . $title . '</a>';
    }
}
