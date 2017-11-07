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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_ActiveRules extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select {

  protected function _getOptions() {
    $options = array();

    $options[] = array('value' => '', 'label' => '');
    $options[] = array('value' => 1, 'label' => 'Yes');
    $options[] = array('value' => 0, 'label' => 'No');

    return $options;
  }

  public function getCondition() {
    
    $value = $this->getValue();

    $rulesIds = array();

    //Manage sub category filter
    $collection = Mage::getModel('CrmTicket/EmailRouterRules')
            ->getCollection()
            ->addFieldToFilter('cerr_active', $value);

    foreach ($collection as $rule) {
      if ($rule) {
        $rulesIds[] = $rule->getId();
      }
    }

    if (count($rulesIds) > 0)
      return array('in' => $rulesIds);
    else
      return null;
  }

}