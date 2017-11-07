<?php

class MDN_Quotation_Model_Observer {

    /**
     * Cron to remind customer and store bought tag
     *
     * @param Varien_Event_Observer $observer
     */
    public function customer_reminder() {
        
        $reminderEnabled = Mage::getStoreConfig('quotation/customer_reminder/enable', 0);
        $reminderDelay = Mage::getStoreConfig('quotation/customer_reminder/delay', 0);

        $reminderModel = Mage::getModel('Quotation/Quotation_Reminder');

        //get visible and not bought quote
        $collection = Mage::getModel('Quotation/Quotation')
                        ->getCollection()
                        ->addFieldToFilter('bought', 0)
                        ->addFieldToFilter('product_id', array('gt' => 0))
                        ->addFieldToFilter('status', MDN_Quotation_Model_Quotation::STATUS_ACTIVE);

        foreach ($collection as $quote) {

            if ($reminderEnabled && ($quote->getreminded() == 0)) {
                $reminderModel->checkForSendReminder($quote);
            }

            $quote->checkForQuoteIsBought();
        }
    }

   

}

