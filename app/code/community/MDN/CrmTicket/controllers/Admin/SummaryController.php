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
class MDN_CrmTicket_Admin_SummaryController extends Mage_Adminhtml_Controller_Action {

    /**
     * display statistics by user
     */
    public function UserAction() {
        $this->loadLayout();
        $this->_setActiveMenu('crmticket');
        $this->renderLayout();
    }

    /**
     * display statistics by category
     */
    public function CategoryAction() {

        $this->loadLayout();
        $this->_setActiveMenu('crmticket');
        $this->renderLayout();
    }

    protected function _isAllowed() {
        return true;
    }
}
