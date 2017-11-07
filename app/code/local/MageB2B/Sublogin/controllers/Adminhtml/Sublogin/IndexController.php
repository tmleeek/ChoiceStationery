<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Adminhtml_Sublogin_IndexController extends Mage_Adminhtml_Controller_Action
{
    
    protected function _isAllowed()
    {
        return true;
        return Mage::getSingleton('admin/session')->isAllowed('admin/customer/sublogin');
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('customer/sublogin')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Sublogin Manager'));
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('sublogin/admin_sublogin_index'));
        $this->renderLayout();
    }

    public function gridAction()
    {
        $id = $this->getRequest()->getParam('id');
        Mage::register('current_customer', Mage::getModel('customer/customer')->load($id));
        echo Mage::getSingleton('core/layout')->createBlock('sublogin/customer_edit_tab_sublogin_grid')->toHtml();
    }

    public function newAction()
    {
        $id = $this->getRequest()->getParam('cid');
        Mage::register('current_customer', Mage::getModel('customer/customer')->load($id));

        $model = Mage::getModel('sublogin/sublogin');
        $model->setEntityId(Mage::registry('current_customer')->getId());

        $this->editAction($model);
    }

    public function editAction($model=null)
    {
        if (!$model)
        {
            $id = $this->getRequest()->getParam('id');
            $model = Mage::getModel('sublogin/sublogin')->load($id);
            if (!$model->getId())
            {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sublogin')->__('Item does not exist'));
                return;
            }

            $id = $this->getRequest()->getParam('cid');
            if (!$id)
                $id = $model->getEntityId();
            Mage::register('current_customer', Mage::getModel('customer/customer')->load($id));
        }

        Mage::register('subloginModel', $model);
        $this->loadLayout();

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('sublogin/customer_edit_tab_sublogin_edit'));

        $this->renderLayout();
    }


    public function saveAction()
    {
        if (!$this->getRequest()->getPost()) {
            $this->_redirect('*/*/');
        }

        $id = (int)$this->getRequest()->getParam('id');
        $model = Mage::getModel('sublogin/sublogin')->load($id);

		$postData = $this->getRequest()->getPost();
		$postData = $this->setMissingParams($postData);
		
		if (isset($postData['address_ids']) && is_array($postData['address_ids']))
        {
            $postData['address_ids'] = implode(',',$postData['address_ids']);
        }
        
        if (isset($postData['acl']) && is_array($postData['acl']))
        {
            $postData['acl'] = implode(',',$postData['acl']);
        }
		
		$password = trim($postData['password']);
        if (empty($password) || $password == "******") {
            unset($postData['password']);
        } else {
			$postData['password'] = Mage::helper('core')->getHash($password, 2);
			if ($model->getId())
			{
				// set un-encrypted password in model so it can be used in email
				$model->setData('password', $password);
				Mage::helper('sublogin/email')->sendNewPasswordEmail($model);
			}
		}
		
        if (empty($postData['expire_date'])) {
            $postData['expire_date'] = '0000-00-00';
        }
        if (!isset($postData['send_backendmails'])) {
            $postData['send_backendmails'] = false;
        }
        if (!isset($postData['create_sublogins'])) {
            $postData['create_sublogins'] = false;
        }
        if (!isset($postData['active'])) {
            $postData['active'] = false;
        }
        if (!isset($postData['order_needs_approval'])) {
            $postData['order_needs_approval'] = false;
        }
        $model->setData($postData);
        
        $sendnewsublogin = false;
        if (!$model->getId() && $model->getData('send_backendmails'))
        {
			$sendnewsublogin = true;
		}
        
        $model->save();
        
        // send new sublogin email
        if ($sendnewsublogin)
        {
			$model->setData('password', $password);
			Mage::helper('sublogin/email')->sendNewSubloginEmail($model);
		}

        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));
		
		if (isset($postData['from_transfer'])) {
			// as its from transfer, then delete customer who is transferred to sublogin
			// get customer data before deleting it
			$subloginCustomerId = $postData['sublogin_customer'];
			$subloginCustomer = Mage::getModel('customer/customer')->load($subloginCustomerId);
			$subloginCustomerData = $subloginCustomer->getData();
			$subloginCustomer->delete();
			
			return $this->_redirect('*/*/transfer');
		} 
        $this->_redirect('*/*/edit', array('id'=>$model->getId(), 'cid'=>$model->getEntityId()));
        return;
    }

    public function gridMassDeleteAction()
    {
        $ids = $this->getRequest()->getParam('sublogins');
        if (!is_array($ids))
        {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sublogin')->__('Please select Sublogins.'));
        }
        else
        {
            foreach ($ids as $id)
            {
                $model = Mage::getModel('sublogin/sublogin')->load($id);
                if ($model->getId())
                    $model->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('sublogin')->__('Deletion successful'));
        }

        $id = $this->getRequest()->getParam('id');
        $this->_redirect('customer/entity/edit', array('id'=>$id));
    }

    /**
     * delete
     * @throws Exception
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('sublogin/sublogin')->load($id);
        $model->delete();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
        $this->_redirect('adminhtml/sublogin_index');
    }

    /**
     * action called from sublogin view, actions
     * @throws Exception
     */
    public function deleteSingleSubloginAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('sublogin/sublogin')->load($id);
        $model->delete();

        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
        $this->_redirect('adminhtml/sublogin_index');
    }
	  /**
     * get customer information
     */
	public function customerautosuggestAction()
    {
		$term = $this->getRequest()->getParam("term");
		
		 $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('customer_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left');
        if ($this->websiteFilter)
            $collection->addFieldToFilter('website_id', array('in', array($this->websiteFilter)));
		
		//$collection->addAttributeToFilter(['name','firstname','lastname'], [ ['like' => '%'.$term.'%'],['like' => '%'.$term.'%'],['like' => '%'.$term.'%'] ]);
		$collection->addAttributeToFilter('name', [ ['like' => '%'.$term.'%']]);
        $ret = array();
        foreach ($collection as $item)
        {
            $label = $item->getName();
            if($item->getBillingPostcode())
                $label .= ' / ' . $item->getBillingCity() . ' / ' . $item->getBillingPostcode();
            if($cId = $this->formatCustomerId($item->getCustomerId()))
                $label .= ' / '. $cId;
			
            $ret[ $item->getId()] = array(
                'id' => $item->getId(),
                'value' => $label,
            );
        }

        echo json_encode($ret);
	}
	/**
     * Retrieve allowed customers
     *
     * @param int $customerId  return name by customer id
     * @return array|string
     */
    protected function formatCustomerId($customerId)
    {
        if (!Mage::getStoreConfig('customer_id/customer_id/auto_increment'))
            return $customerId;
        if (!Mage::getStoreConfig('customer_id/customer_id/template_force_apply'))
            return $customerId;
        if (!$customerId)
            return $customerId;

        // no number inside customer_id
        if (!preg_match('/[1-9]+[0-9]*/', $customerId, $result))
            return $customerId;
        $number = $result[0];
        $template = Mage::getStoreConfig('customer_id/customer_id/id_template');
        $p = sscanf($customerId, $template);
        if (!is_array($p) || !isset($p[0]) || !$p[0])
        {
            return sprintf($template, $number);
        }
        return $customerId;
    }
    /**
     * get customer information
     */
	public function customerinfoAction()
    {
		$customerId = $this->getRequest()->getParam('customer_id');
		$ret = array();
		if ($customerId) {
			$customer = Mage::getModel('customer/customer')->load($customerId);
			if ($customer->getId()) {
				$website = Mage::getModel('core/website')->load($customer->getWebsiteId());
				$storeOptions = array();
				foreach ($website->getStoreCollection() as $store)
				{
					$storeOptions[] = "<option value='".$store->getId()."'>".$store->getCode()."</option>";
				}
				$ret['storeOptions'] = implode("", $storeOptions);
				
				$customerAddresses = array();
				foreach ($customer->getAddresses() as $address)
				{
					$street = '';
					$customerStreet = $address->getStreet();
					foreach ($customerStreet as $entry)
					{
						$street .= isset($entry) ? $entry : '';
						$street .= ' ';
					}
					$html = $address->getCompany() . ' ' . $street  . ' ' . $address->getPostcode()  . ' ' . $address->getCity();
					$customerAddresses[] = "<option value='".$address->getId()."'>".$html."</option>";
				}
				$ret['customerAddresses'] = implode("", $customerAddresses);
			}
		}
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($ret));
	}

    /**
     * @param $postData
     * @return mixed
     */
	protected function setMissingParams($postData)
	{
		$defaultValues = Mage::getModel('sublogin/source_formfields')->getDefaultValues();
		foreach ($defaultValues as $key => $defaultValue) {
			if (!isset($postData[$key])) {
				$postData[$key] = $defaultValue;
			}
		}
		return $postData;
	}

    public function approveOrderAction()
    {
        $orderIds = $this->getRequest()->getParam('order_ids');
        if (!is_array($orderIds))
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sublogin')->__('Please select order(s).'));
        }
        else
        {
            foreach ($orderIds as $id)
            {
                $orderModel = Mage::getModel('sales/order')->load($id);
                $orderModel->setState('approved');
                $orderModel->setStatus('approved');
                $orderModel->sendNewOrderEmail();
                $orderModel->save();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('sublogin')->__('Changed order(s) to approved.'));
        }
        $this->_redirect('adminhtml/sales_order/index');
    }

    public function declineOrderAction()
    {
        $orderIds = $this->getRequest()->getParam('order_ids');
        if (!is_array($orderIds))
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sublogin')->__('Please select order(s).'));
        }
        else
        {
            foreach ($orderIds as $id)
            {
                $orderModel = Mage::getModel('sales/order')->load($id);
                $orderModel->setState('not_approved');
                $orderModel->setStatus('not_approved');
                $orderModel->save();
                // now inform about declining order
                
                $subloginId = $orderModel->getSubloginId();
                if ($subloginId) {
                    $subloginModel = Mage::getModel('sublogin/sublogin')->load($subloginId);
                    $subloginModel->setData('order_id', $orderModel->getId());
                    $subloginModel->setData('order_increment_id', $orderModel->getIncrementId());
                    Mage::helper('sublogin/email')->sendOrderDeclinedEmailAlert($subloginModel);
                }
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('sublogin')->__('Changed order(s) to declined.'));
        }
        $this->_redirect('adminhtml/sales_order/index');
    }

	public function transferAction()
    {
		$this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('sublogin/admin_transfer'));
        $this->renderLayout();
    }
	
	public function subloginfieldsAction()
	{
		$fieldsHtml = $this->getLayout()->createBlock('sublogin/admin_transfer_subloginfields')->toHtml();
		
		$return['fieldsHtml'] = $fieldsHtml;
		return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($return));
	}
	
	public function isSubloginEmailUniqueAction()
	{
		$post = $this->getRequest()->getPost();
		$email = $post['email'];
		
		$sublogin = Mage::getModel('sublogin/sublogin')->load($email, 'email');
		if ($sublogin->getId()) {
			$return['isUnique'] = 0;
		} else {
			$return['isUnique'] = 1;
		}
		
		return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($return));
	}
}