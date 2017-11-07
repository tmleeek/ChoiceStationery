<?php

class Mxm_AllInOne_Model_Roi_Tracker
{
    /**
     * Complete the tracking by telling the ROI helper to append the complete script
     */
    public function completeTracking()
    {
        $helper = Mage::helper('mxmallinone/roi');
        if (!$helper->isEnabled()) {
            return;
        }

        $lastOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order       = Mage::getSingleton('sales/order');
        $order->load($lastOrderId);

        $helper->setComplete($order->getGrandTotal());
    }
}
