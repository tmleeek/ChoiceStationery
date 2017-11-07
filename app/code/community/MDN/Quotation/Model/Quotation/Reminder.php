<?php

class MDN_Quotation_Model_Quotation_Reminder extends Mage_Core_Model_Abstract {

    /**
     * Check if we can remind customer
     */
    public function checkForSendReminder($quote) {
        if ($quote->getnotification_date() != '') {
            $reminderDelay = Mage::getStoreConfig('quotation/customer_reminder/delay', 0);
            $now = date('Y-m-d');
            $quote_notificaiton_date = date('Y-m-d', strtotime($quote->getnotification_date()));
            $delay = Mage::helper('quotation/Tools')->daysBetweenDates($quote_notificaiton_date, $now);

            if ($delay > $reminderDelay) {
                $this->sendCustomerReminder($quote);
                return true;
            }
        }
        return false;
    }

    /**
     * Send reminder to customer
     *
     */
    public function sendCustomerReminder($quote) {
        
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $templateId = Mage::getStoreConfig('quotation/customer_reminder/email_template', $quote->getCustomer()->getStoreId());
        $identityId = Mage::getStoreConfig('quotation/customer_reminder/email_identity', $quote->getCustomer()->getStoreId());

        //var to use in template
        $data = array
            (
            'subject' => Mage::helper('quotation')->__('Quotation reminder'),
            'customer_name' => $quote->getCustomer()->getName(),
            'url' => Mage::getUrl(''),
            'quote_name' => $quote->getcaption(),
            'direct_url' => Mage::helper('quotation/DirectAuth')->getDirectUrl($quote)
        );

        //envoi le mail
        if (!empty($templateId)) {
            Mage::getModel('Quotation/Core_Email_Template')
                    ->setDesignConfig(array('area' => 'adminhtml', 'store' => $quote->getCustomer()->getStoreId()))
                    ->sendTransactionalWithAttachment(
                            $templateId,
                            $identityId,
                            $quote->getCustomer()->getemail(),
                            null,
                            $data,
                            null);
        } else {
            throw new Exception('Empty email template.');
        }

        $translate->setTranslateInline(true);
        $quote->setreminded(1)->save();
        $quote->addHistory(Mage::helper('quotation')->__('Customer reminded'));

        return $this;
    }

}