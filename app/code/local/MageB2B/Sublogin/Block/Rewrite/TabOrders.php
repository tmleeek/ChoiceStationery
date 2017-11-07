<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Rewrite_TabOrders extends Mage_Adminhtml_Block_Customer_Edit_Tab_Orders
{
	public function setCollection($collection)
    {
		$collection->addFieldToSelect('sublogin_id');
		parent::setCollection($collection);
	}

	protected function _prepareColumns()
    {
		parent::_prepareColumns();
		// get sublogins
		$subloginCollection = Mage::getModel('sublogin/sublogin')->getCollection();
        if (Mage::registry('current_customer')->getId())
        {
            $subloginCollection->addFieldToFilter('entity_id', Mage::registry('current_customer')->getId());
        }
		$subloginOptions = array();
		foreach($subloginCollection as $subloginModel)
        {
			$subloginOptions[$subloginModel->getId()] = $subloginModel->getEmail();
		}
		
		$this->addColumnAfter('sublogin_id', array(
				'header'    => Mage::helper('customer')->__('Sublogin Email'),
				'index'     => 'sublogin_id',
				'type'      => 'options',
				'options'   => $subloginOptions,
			),
			'created_at'
		);

		$this->sortColumnsByOrder();
		return $this;
	}
}
