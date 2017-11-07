<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Source_Emailtemplates
{
    /*
     * email templates
     */
	protected $_emailTemplates = array(
		'sublogin/email/new' => 'Sublogin new email',
		'sublogin/email/reset_password' => 'Sublogin reset password',
		'sublogin/email/mainlogin_orderalert' => 'Sublogin mainlogin order alert',
		'sublogin/email/order_require_approval' => 'Sublogin require approval',
		'sublogin/email/expire_refresh' => 'Sublogin expire refresh',
		'sublogin/email/order_declined' => 'Sublogin order declined',
	);

    /**
     * Options getter
     * @return array
     */

    public function getAllOptions()
    {
		$options = array();
		foreach($this->_emailTemplates as $key => $value)
		{
			$options[] = array(
				'label'	=>	Mage::helper('sublogin')->__($value),
				'value'	=>	$key,
			);
		}
		return $options;
    }

	public function toOptionArray()
	{
		return $this->getAllOptions();
	}
}
