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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_QuickActions extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

  public function render(Varien_Object $ticket) {

    $html = '<div>';
    $ticket_id = $ticket->getct_id();
    $customer_id = $ticket->getCustomer()->getId();

    if ($ticket_id) {
      $actionList = $ticket->getQuickActions();

      $helper = Mage::helper('CrmTicket');

      if ($actionList) {
        $qaKey = 'qa_' . $ticket_id;
        $params = array('ticket_id' => $ticket_id, 'customer_id' => $customer_id);

        $html .= '<select onchange="eval(this.value)" name="' . $qaKey . '" id="' . $qaKey . '">';

        //to avoid to have the 1st option selected by default
        $html .= '<option value=""><i>' . $helper->__('Please select') . '</i></option>';

        //Opt Group List
        $optgroup = array();
        foreach ($actionList as $qaObject) {
          if ($qaObject) {
            $groupLabel = $qaObject->getQuickActionGroup();

            if (!in_array($groupLabel, $optgroup)) {
              $optgroup[$groupLabel] = 'toAdd';
            }
          }
        }

        //Display actions by group
        foreach ($optgroup as $groupName => $groupStatus) {
          foreach ($actionList as $qaObject) {

            if ($qaObject) {
              if ($groupName == $qaObject->getQuickActionGroup()) {
                if ($groupStatus == 'toAdd') {
                  $html .= '<optgroup label=\'' . $groupName . '\'>';
                  $optgroup[$groupLabel] = $groupStatus = 'Added';
                }
                $actions = $qaObject->getQuickActions($params);
                foreach ($actions as $qa) {

                  $value = $qa[MDN_CrmTicket_Model_QuickAction_Abstract::ITEM_URL];
                  $label = $qa[MDN_CrmTicket_Model_QuickAction_Abstract::ITEM_LABEL];
                  $html .= '<option value="' . $value . '" >' . $label . '</option>';
                }                
              }
            }
          }
          $html .= '</optgroup>';
        }

        $html .= '</select>';
      }
    }

    $html .= '</div>';

    return $html;
  }

}
