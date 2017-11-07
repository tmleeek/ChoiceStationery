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
class MDN_CrmTicket_Model_EmailAccountRouterRules extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('CrmTicket/EmailAccountRouterRules','cearr_id');
    }

    public function getEmailAccountRule($storeId,$catId) {

        $collection = Mage::getModel('CrmTicket/EmailAccountRouterRules')
                ->getCollection()
                ->addFieldToFilter('cearr_store_id', $storeId)
                ->addFieldToFilter('cearr_category_id', $catId);

        $rule = null;

        if(count($collection) == 1 ){
          $rule = $collection->getFirstItem();
        }
               

        return $rule;
    }
}
