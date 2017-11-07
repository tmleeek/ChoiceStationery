<?php

class Mxm_AllInOne_Model_Cron
{
    /**
     * Called once a minute by Magento's cron system
     */
    public function run()
    {
        try {
            // Ensure the customer space is set up correctly in Maxemail
            Mage::getSingleton('mxmallinone/setup')->run();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        try {
            // Run all of the scheduled sync types
            Mage::helper('mxmallinone/sync')->runScheduled();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
