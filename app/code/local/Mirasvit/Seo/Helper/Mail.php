<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_seo
 * @version   1.3.18
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


class Mirasvit_Seo_Helper_Mail
{
	public $emails = array();
	protected function getConfig() {
		return Mage::getSingleton('seo/config');
	}

	protected function getSender() {
		return 'general';
	}

    protected function send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables) {
		if (!$senderEmail) {
			return false;
		}
        $template = Mage::getModel('core/email_template');
        $template->setDesignConfig(array('area' => 'backend'))
                 ->sendTransactional($templateName,
                 array(
                     'name' => $senderName,
                     'email' => $senderEmail
                 ),
                 $recipientEmail, $recipientName, $variables);
		$text = $template->getProcessedTemplate($variables, true);
		$this->emails[]= array('text'=>$text, 'recipient_email'=>$recipientEmail, 'recipient_name'=>$recipientName);
		return true;
    }


    /************************/

}