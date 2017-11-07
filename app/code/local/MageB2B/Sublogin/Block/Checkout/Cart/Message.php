<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Checkout_Cart_Message extends Mage_Checkout_Block_Cart
{
	public function __construct()
	{
		$subloginModel = Mage::helper('sublogin')->getCurrentSublogin();
        if (!is_null($subloginModel) && $subloginModel->getId()) {
			if (Mage::getStoreConfig('sublogin/general/order_approval')) {
                if ($subloginModel->getOrderNeedsApproval()) {
					$notice = trim(Mage::getStoreConfig('sublogin/general/order_approval_cart_notice'));
					if (!empty($notice))
					{
						Mage::getSingleton('checkout/session')->addNotice(Mage::helper('sublogin')->__($notice));
					}
				}
			}
		}
	}
}
