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
 * @copyright  Copyright (c) 2013 BoostMyshop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Model_DefaultReply extends Mage_Core_Model_Abstract {

  public function _construct() {
    $this->_init('CrmTicket/DefaultReply', 'cdr_id');
  }

  public function getDefaultReplys() {
    $defaultReplys = array();

    $collection = $this->getCollection()->setOrder('cdr_name', 'asc');

    foreach ($collection as $dr) {
      $defaultReplys[$dr->getcdr_id()] = $dr->getcdr_name();
    }

    return $defaultReplys;
  }

  public function getReplyTextById($id) {

    $text = '';

    $reply = $this->getCollection()->AddFieldToFilter('cdr_id', $id)->getFirstItem();
    if ($reply->getId()) {
      $text = $reply->getcdr_content();
    }
   
    return $text;
  }

  public function getReplyNameById($id) {

    $text = '';

    $reply = $this->getCollection()->AddFieldToFilter('cdr_id', $id)->getFirstItem();
    if ($reply->getId()) {
      $text = $reply->getcdr_name();
    }

    return $text;
  }

}
