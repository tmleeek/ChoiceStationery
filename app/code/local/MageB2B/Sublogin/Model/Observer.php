<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Observer
{
    /**
     * TODO: Delete this method as its not used any more
     * After loading a customer with sublogin set the email and passwordhash
     * @param Varien_Event_Observer $observer customer_load_after
     */
    public function customerLoadAfter($observer)
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return;
        }
        if (Mage::registry('noAttributeOverride')) {
            return;// used in customerSaveBefore
        }
        
        // Subogin extension is conflicting with Enterprise_Reward extension of Magento Enterprise. So following is the hack to solve that conflict
        if (Mage::helper('core')->isModuleEnabled('Enterprise_Reward'))
        {
            $debugBackTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);
            foreach ($debugBackTrace as $debugBackTraceItem) {
                if ($debugBackTraceItem['class'] == 'Enterprise_Reward_Model_Observer' && $debugBackTraceItem['function'] == 'checkRates') {
                    return $this;
                }
            }
        }
        
        $customer = $observer->getCustomer();
        $sublogin = Mage::helper('sublogin')->getCurrentSublogin($customer);
        
        // check whether sublogin exist when sublogin is logged-in, if not then logout the customer
        if (Mage::getSingleton('customer/session')->getData('sublogin_email') && Mage::getSingleton('customer/session')->getData('sublogin_email') != $customer->getData('email')) // this means, sublogin is logged-in
        {           
            if (!$sublogin) // sublogin is not exist, so logout the customer
            {
                Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('Your account does not exist.', $customer->getData('email')));
                
                Mage::app()->getResponse()->setRedirect(Mage::getUrl("customer/account/logout"));
                return;
            }
            
            if (!$sublogin->getActive())
            {
                Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('Your account is deactivated.', $customer->getData('email')));
                
                Mage::app()->getResponse()->setRedirect(Mage::getUrl("customer/account/logout"));
                return;
            }
        }        
        
        if (Mage::getSingleton('customer/session')->getId() == $customer->getId()) {
            Mage::helper('sublogin')->setFrontendLoadAttributes($sublogin, $customer);
        }
    }

    /**
     *
     * - when a sublogin changes a customer in the frontend, it should update the sublogin-information
     * - when a customer registers in the frontend it should check wether a sublogin emailaddress
     * already exists and throw an error - this is also done from inside the backend
     *
     * @param Varien_Event_Observer $observer customer_save_before
     */
    public function customerSaveBefore($observer)
    {
        /**
		 * Skip code execution if its called from onepage controller
		 */
		$debugBackTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);
		foreach ($debugBackTrace as $debugBackTraceItem) {
			if ($debugBackTraceItem['class'] == 'Mage_Checkout_OnepageController' && $debugBackTraceItem['function'] == 'saveOrderAction') {
				return $this;
			}
		}
		
        $customer = $observer->getCustomer();
        // without noAttributeOverride there is no "clean" way to access the original email
        // I need the original email here, so that it doesn't get overwritten
        Mage::register('noAttributeOverride', true);
        if ($email = Mage::getSingleton('customer/session')->getSubloginEmail()) {
            // update password and email - the password is already hashed through magento
            $sublogin = Mage::helper('sublogin')->getCurrentSublogin($customer);
            if ($sublogin) {
                // when a password is set, it exists also in a hashed way in the customer-model
                if ($customer->getData('password')) {
                    $sublogin->setPassword($customer->getData('password_hash'));
                }

                foreach (array('firstname', 'lastname', 'rp_token', 'rp_token_created_at') as $t) {
                    $sublogin->setData($t, $customer->getData($t));
                }

                // check email collision
                // and set email
                if ($customer->getData('email') != $sublogin->getEmail()) {
                    if (!Mage::helper('sublogin')->validateUniqueEmail($customer->getData('email'), $customer->getWebsiteId())) {
                        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sublogin')->__('Email "%s" already exists', $customer->getData('email')));
                    } else {
                        $sublogin->setEmail($customer->getData('email'));
                        Mage::getSingleton('customer/session')->setSubloginEmail($sublogin->getEmail());
                    }
                }
                // check subscription and delete or add newsletter subscription
                // all subscription related changes are handeled while saving sublogin
                $sublogin->save();
            }
            // save the original data of the customer object to restore it in customer save after, to prevent side effects
            // from changing the customer object (which is for example stored in the customers session)
            Mage::register('original_customer_data', $customer->getData());
            // a sublogin shouldn't change the account email and password and rp_token
            // load the original customer without overwriting his email
            $origCustomer = Mage::getModel('customer/customer')->load($customer->getId());
            $customer->setData('password_hash', $origCustomer->getData('password_hash'));
            $customer->setData('password', '');
            foreach (array('prefix', 'is_subscribed', 'firstname', 'lastname', 'email', 'active', 'expire_date', 'customer_id', 'rp_token', 'rp_token_created_at') as $value) {
                $customer->setData($value, $origCustomer->getData($value));
            }
        }

        Mage::unregister("noAttributeOverride");
        // when registering - check if sublogin with the same email exists - for reference look also at Mage_Customer_Model_Resource_Customer::_beforeSave
        $websiteId = $customer->getWebsiteId() ? $customer->getWebsiteId() : Mage::app()->getStore()->getWebsiteId();
        if (!Mage::helper('sublogin')->validateUniqueEmail($customer->getEmail(), $websiteId, $customer->getId())) {
            throw Mage::exception(
                'Mage_Customer', Mage::helper('customer')->__('This customer email already exists'),
                Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS
            );
        }

        $customerSubloginAttributes = Mage::app()->getRequest()->getParam('sublogin');
        $customerSubloginKeys = array('can_create_sublogins', 'max_number_sublogins', 'sublogin_optional_email');
        foreach ($customerSubloginKeys as $attribute) {
            if (isset($customerSubloginAttributes[$attribute])) {
                $customer->setData($attribute, $customerSubloginAttributes[$attribute]);
            }
        }
        
        // set mainlogin budget in customer attribute budgets
        if (Mage::app()->getStore()->isAdmin()) {
            $budget = Mage::app()->getRequest()->getPost('budget');
            // $budgets = isset($budget['budgets']) ? json_encode($budget['budgets']) : null;
            $budgets = isset($budget['budgets']) ? $budget['budgets'] : null;
            if ($budgets)
            {
                foreach ($budgets as $k=>$budget) {
                    foreach ($budgets as $kSub=>$budgetSub) {
                        if ($k==$kSub) {
                            continue;
                        }
                        
                        if ($budget['budget_type'] == $budgetSub['budget_type'])
                        {
                            if ($budget[$budget['budget_type']] == $budgetSub[$budgetSub['budget_type']])
                            {
                                Mage::throwException(Mage::helper('sublogin')->__('Some budgets are duplicated either by year, month or day.'));
                            }
                        }
                    }
                    
                    $budget = Mage::helper('sublogin/budget')->manipulateDataBasedonBudgetType($budget);
                    $budgets[$k] = $budget;
                }
                $budgets = json_encode($budgets);
            }
            
            $customer->setData('budgets', $budgets);
        }
    }

    /**
     * TODO: add condition to first return if not admin area
     *
     * when saving a customer in the backend
     */
    public function customerSaveAfter($observer)
    {
        $customer = $observer->getCustomer();
        if (Mage::app()->getStore()->isAdmin()) {
            // save all sublogins in admin
            $request = Mage::app()->getRequest();
            if ($sublogins = $request->getParam('sublogin')) {
                if (isset($sublogins['sublogins'])) {
                    $sublogins = $sublogins['sublogins'];
                    foreach ($sublogins as $subloginData) {
                        $sendnewsublogin = false;
                        $sendnewpassword = false;
                        $sublogin = Mage::getModel('sublogin/sublogin');
                        foreach ($subloginData as $key => $value) {
                            // this is because of address_ids (its an array)
                            if (!is_array($value))
                                $subloginData[$key] = trim($value);
                        }
                        // load sublogin model
                        if ($subloginData['id']) {
                            $sublogin->load($subloginData['id']);
                        }
                        // if delete, delete model and continue in loop
                        if ($subloginData['delete']) {
                            $sublogin->delete();
                            continue;
                        }
                        $sublogin->setEntityId($customer->getId());
                        // check email collision
                        if ($sublogin->getEmail() != $subloginData['email']) {
                            if (!Mage::helper('sublogin')->validateUniqueEmail($subloginData['email'], $customer->getWebsiteId())) {
                                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sublogin')->__('Sublogin with Email "%s" already exists', $subloginData['email']));
                                continue;
                            }
                            $sublogin->setEmail($subloginData['email']);
                        }
                        if ($subloginData['password']) {
                            $sublogin->setPassword(Mage::helper('core')->getHash($subloginData['password'], 2));
                        }
                        $attributes = array(
                            'send_backendmails', 'prefix', 'is_subscribed', 'firstname', 'lastname',
                            'expire_date', 'active', 'store_id', 'create_sublogins', 'order_needs_approval', 'optional_email'
                        );

                        foreach ($attributes as $attribute) {
                            $value = isset($subloginData[$attribute]) ? $subloginData[$attribute] : false;
                            $sublogin->setData($attribute, $value);
                        }
                        if (isset($subloginData['address_ids'])) {
                            $addressIds = @implode(',', $subloginData['address_ids']);
                            $sublogin->setData('address_ids', $addressIds);
                        }
                        if (isset($subloginData['acl'])) {
                            $acls = @implode(',', $subloginData['acl']);
                            $sublogin->setData('acl', $acls);
                        }
                        $sublogin->save();
                        // send email about newly created sublogin
                        if (!$subloginData['id']) {
                            $sendnewsublogin = true;
                        } // send email about password reset
                        else if ($subloginData['password']) {
                            $sendnewpassword = true;
                        }
                        if (($sendnewpassword || $sendnewsublogin) && $sublogin->getData('send_backendmails')) {
                            // change password to plaintext so it can be displayed
                            $sublogin->setPassword($subloginData['password']);
                            if ($sendnewsublogin) {
                                Mage::helper('sublogin/email')->sendNewSubloginEmail($sublogin);
                                
                            } else if ($sendnewpassword) {
                                Mage::helper('sublogin/email')->sendNewPasswordEmail($sublogin);
                            }
                        }
                    }
                }
            }
        }
        // restore the original sublogin data
        if (Mage::registry('original_customer_data')) {
            $customer->setData(Mage::registry('original_customer_data'));
            Mage::unregister('original_customer_data');
        }
    }

    /**
     * reset the sublogin email from the customer session
     * 
     * @Varien_Event_Observer customer_logout
     */
    public function customerLogout($observer)
    {
        Mage::getSingleton('customer/session')->setSubloginEmail(false);
        Mage::getSingleton('customer/session')->setLoggedinFromMainLogin(false);
    }

    /**
     * display customer sublogin tab in backend
     * @Varien_Event_Observer core_block_abstract_to_html_before
     */
    public function toHtmlBefore($observer)
    {
        $block = $observer->getBlock();
        if ($block->getId() == 'customer_info_tabs') {
            $block->addTab('sublogin', array(
                'label'   => Mage::helper('sublogin')->__('Sublogins'),
                'title'   => Mage::helper('sublogin')->__('Sublogins'),
                'content' => $block->getLayout()->createBlock('sublogin/customer_edit_tab_sublogin')->toHtml(),
            ));
            
            $block->addTab('sublogin_budget', array(
                'label'   => Mage::helper('sublogin')->__('Budget'),
                'title'   => Mage::helper('sublogin')->__('Budget'),
                'content' => $block->getLayout()->createBlock('sublogin/customer_edit_tab_budget')->toHtml(),
            ));
        }
    }

    /**
     * deletes all sublogins when customer is deleted
     *
     * @param Varien_Event_Observer $observer customer_delete_after
     */
    public function customerDeleteAfter($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $subloginCollection = Mage::getModel('sublogin/sublogin')->getCollection()
            ->addFieldToFilter('entity_id', $customer->getId());
        foreach ($subloginCollection as $sublogin) {
            $sublogin->delete();
        }
    }

    /**
     * this function sets display of customer menu sublogins on/off depending on setting or
     * if the current customer is a sublogin or not
     *
     * @param Varien_Event_Observer $observer controller_action_layout_load_before
     *
     */
    public function addHandle(Varien_Event_Observer $observer)
    {
		if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
			return null;
		}
		
        $subloginHelper = Mage::helper('sublogin');
        if ($subloginHelper->canAccessSubloginMenu()) {
            $observer->getEvent()->getLayout()->getUpdate()->addHandle('customer_account_navigation_sublogin');
        }
		
		if (Mage::registry('noAttributeOverride')) {
            return;// used in customerSaveBefore
        }
        
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $sublogin = Mage::helper('sublogin')->getCurrentSublogin($customer);
        
        // check whether sublogin exist when sublogin is logged-in, if not then logout the customer
        if (Mage::getSingleton('customer/session')->getData('sublogin_email') && Mage::getSingleton('customer/session')->getData('sublogin_email') != $customer->getData('email')) // this means, sublogin is logged-in
        {           
            if (!$sublogin) // sublogin is not exist, so logout the customer
            {
                Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('Your account does not exist.', $customer->getData('email')));
                
                Mage::app()->getResponse()->setRedirect(Mage::getUrl("customer/account/logout"));
                return;
            }
            
            if (!$sublogin->getActive())
            {
                Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('Your account is deactivated.', $customer->getData('email')));
                
                Mage::app()->getResponse()->setRedirect(Mage::getUrl("customer/account/logout"));
                return;
            }
        }        
        
        if (Mage::getSingleton('customer/session')->getId() == $customer->getId()) {
            Mage::helper('sublogin')->setFrontendLoadAttributes($sublogin, $customer);
        }
    }

    /**
     * we need to set the data to the order object before its initially saved
     * observer before checkout
     *
     * @param Varien_Event_Observer $observer checkout_type_onepage_save_order
     */
    public function checkoutSaveBefore($observer)
    {
        $subloginModel = Mage::helper('sublogin')->getCurrentSublogin();
        if (!is_null($subloginModel) && $subloginModel->getId()) {
            $order = $observer->getOrder();
            $order->setData('sublogin_id', $subloginModel->getId());
			if (Mage::getStoreConfig('sublogin/general/save_customer_email_in_order')) {
				$order->setCustomerEmail(Mage::getSingleton('customer/session')->getData('orig_email'));
			}
            if (Mage::getStoreConfig('sublogin/general/order_approval')) {
                if ($subloginModel->getOrderNeedsApproval()) {
                    // don't send out new order email if order needs approval
                    $order->setCanSendNewEmailFlag(false);
                }
            }
        }
    }

    /**
     * adds mass actions buttons to admin sales order overview
     * to approve or decline order
     *
     * @param Varien_Event_Observer $observer core_block_abstract_prepare_layout_before
     */
    public function addMassActionApproval($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if(get_class($block) =='Mage_Adminhtml_Block_Widget_Grid_Massaction'
            && $block->getRequest()->getControllerName() == 'sales_order')
        {
            $block->addItem('sublogin_approve_order', array(
                'label' => 'Approve Order',
                'url' => Mage::app()->getStore()->getUrl('sublogin/adminhtml_index/approveOrder'),
            ));
            $block->addItem('sublogin_decline_order', array(
                'label' => 'Decline Order',
                'url' => Mage::app()->getStore()->getUrl('sublogin/adminhtml_index/declineOrder'),
            ));
        }
    }

    /**
     * change order state and status to approval if order approval is needed
     *
     * @param Varien_Event_Observer $observer sales_order_place_after
     */
    public function subloginSalesOrderPlaceAfter($observer)
    {
        $subloginModel = Mage::helper('sublogin')->getCurrentSublogin();
        if (!is_null($subloginModel) && $subloginModel->getId()) {
            $order = $observer->getOrder();
            // change order state and status to approval if order approval is needed
            if (Mage::getStoreConfig('sublogin/general/order_approval')) {
                if ($subloginModel->getOrderNeedsApproval()) {
                    $order->setState('approval')
                          ->setStatus('approval');

                    // send an email to main login to let him know about the order
                    $subloginModel->setData('order_id', $order->getId());
                    $subloginModel->setData('order_increment_id', $order->getIncrementId());

                    Mage::helper('sublogin/email')->sendMainLoginOrderAlert($subloginModel, $order);
                    Mage::helper('sublogin/email')->sendSubloginOrderRequireApproval($subloginModel);
                }
            }
        
            if (Mage::getStoreConfig('sublogin/general/cc_order_mainlogin')) {

				$subloginCustomerAccount = $subloginModel->getCustomer();
                if ($subloginCustomerAccount && $subloginCustomerAccount->getId()) {
                    $original = $order->getCustomerEmail();
                    $to = $subloginCustomerAccount->getOrigEmail();
                    if ($to) {
                        $order->addStatusHistoryComment('Order confirmation mail also sent to main account ' . $to, false);
						$order->save();
						$order->setCustomerEmail($to);
						$order->sendNewOrderEmail();
						$order->setCustomerEmail($original);
                    }
                }
            }
        }
    }
    
    /**
     * Do not save address in address book when sublogin is placing an order
     * 
     * @param Varien_Event_Observer $observer sales_quote_address_save_before 
     */
    public function subloginSalesQuoteAddressSaveBefore($observer)
    {
        // save_sublogin_address=YES, so can save address in main login's addressbook: default magento behaviour
        // so just return from here
        if (Mage::getStoreConfig('sublogin/general/save_sublogin_address')) {
            return $this;
        }
        
        // save_sublogin_address = NO, so can't save address in main login's addressbook
        // so set save_in_address_book = 0
        $quoteAddress = $observer->getEvent()->getQuoteAddress();
        $currentSublogin = Mage::helper('sublogin')->getCurrentSublogin();
        if ($currentSublogin && $currentSublogin->getId()) {
            $quoteAddress->setData('save_in_address_book', 0);
        }
    }
}
