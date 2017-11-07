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
class MDN_CrmTicket_Model_QuickAction_Abstract extends Mage_Core_Model_Abstract {

  const ITEM_LABEL = 'item_label';
  const ITEM_URL = 'item_url';
  const QA_BACK_STATE = 'status';
  const QA_BACK_LABEL = 'message';

  public function getQuickActionType() {
    throw new Exception('getObjectType must be implemented !');
  }

  public function getQuickActionLabel() {
    throw new Exception('getQuickActionLabel must be implemented !');
  }

  public function getQuickActionUrl() {
    return '*/Admin_Ticket/ExecuteQa';
  }

  public function getQuickActionParams($params) {
    $params['action_type'] = $this->getQuickActionType();
    return $params;
  }

  public function getQuickActionJs($params) {
    $url = Mage::helper('adminhtml')->getUrl($this->getQuickActionUrl(), $this->getQuickActionParams($params));
    return 'window.setLocation(\'' . $url . '\')';
  }  

  public function executeQuickAction($params) {
    throw new Exception('executeQuickAction must be implemented !');
  }

  public function getQuickActions($params) {
    return array(
        array(self::ITEM_LABEL => $this->getQuickActionLabel(),
            self::ITEM_URL => $this->getQuickActionJs($params))
    );
  }

  public function getQuickActionGroup() {
    throw new Exception('executeQuickAction must be implemented !');
  }

}
