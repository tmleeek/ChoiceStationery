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
class MDN_CrmTicket_Model_Ticket_QuickAction extends Mage_Core_Model_Abstract {

  const COMMON_LABEL = 'Common';

  /**
   * Return all QuickActions for one ticket
   *
   * @param type $customerId
   */
  public function getQuickActions($ticket) {
    $qa = array();

    //get Common QA
    $this->getCommonQuickActions($ticket, $qa);

    //get Object specific QA
    $objectQa = $this->getObjectsQuickActions($ticket);

    if (count($objectQa) > 0) {
      $qa = array_merge($qa, $objectQa);
    }

    return $qa;
  }

  public function getCommonQuickActions($ticket, &$qa) {

    if (!$ticket->isEditableByCustomer()) {
      $qa[] = Mage::getModel('CrmTicket/QuickAction_Close');
      $qa[] = Mage::getModel('CrmTicket/QuickAction_DefaultReply');
    } else {
      $qa[] = Mage::getModel('CrmTicket/QuickAction_ReOpen');
    }
    $qa[] = Mage::getModel('CrmTicket/QuickAction_AssignToUser');
  }

  public function getObjectsQuickActions($ticket) {

    $customerObjectClass = trim($ticket->getCustomerObjectClass());

    $objectsActions = array();

    if ($customerObjectClass && strlen($customerObjectClass)>0) {
        try{
          $qaTypes = $customerObjectClass->getQuickActions();

          if($qaTypes){
            foreach ($qaTypes as $id => $qaType) {
              $objectsActions[] = $this->getClassByType($qaType);
            }
          }
        }catch(Exception $ex){
            Mage::log("Object non implemented in Quick actions : customerObjectClass=".$customerObjectClass. " ex=".$ex);
        }
    }
    return $objectsActions;
  }

  /**
   * Return all customer object classes (from config.xml file(s))
   */
  public function getClasses() {
    $retour = array();

    $nodes = Mage::getConfig()->getNode('crmticket/quickaction')->asArray();

    foreach ($nodes as $k => $info) {
      $obj = Mage::getModel($info['class']);
      $retour[] = $obj;
    }

    return $retour;
  }

  /**
   * Get class by type
   * @param type $type
   * @return null
   */
  public function getClassByType($type) {

    foreach ($this->getClasses() as $class) {

      if ($class->getQuickActionType() == $type)
        return $class;
    }
    return null;
  }

}
