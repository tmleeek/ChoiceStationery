<?php

class Mxm_AllInOne_Model_Observer
{
    /**
     * @observes checkout_onepage_controller_success_action
     */
    public function onepageSuccess()
    {
        try {
            Mage::getSingleton('mxmallinone/sca_tracker')->setStageComplete();
            Mage::getSingleton('mxmallinone/roi_tracker')->completeTracking();
        } catch (Exception $e) {
            // Ensure the page still loads no matter what goes wrong here
        }
    }

    /**
     * @observes sales_quote_save_after
     */
    public function quoteSave()
    {
        try {
            Mage::getSingleton('mxmallinone/sca_tracker')->setBasketItems();
        } catch (Exception $e) {
            // Ensure the page still loads no matter what goes wrong here
        }
    }

    /**
     * @observes newsletter_subscriber_save_before
     */
    public function subscriberSave($observer)
    {
        try {
            $subscriber = $observer->getSubscriber();
            if (!$subscriber->getNoSync()) {
                $subscriber->setData('mxm_updated_at', Varien_Date::now());
                if ($subscriber->isObjectNew()) {
                    $subscriber->setData('mxm_created_at', Varien_Date::now());
                }
            }
        } catch (Exception $e) {
            // Ensure the page still loads no matter what goes wrong here
        }
    }

    /**
     * @observes newsletter_subscriber_delete_after
     */
    public function subscriberDelete($observer)
    {
        try {
            if (!Mage::helper('mxmallinone/sync')->isEnabled()) {
                return;
            }
            $subscriber = $observer->getSubscriber();
            Mage::helper('mxmallinone')->getApi()
                ->list
                ->unsubscribeRecipientByEmailAddress($subscriber->getEmail());
        } catch (Exception $e) {
            // Ensure the page still loads no matter what goes wrong here
        }
    }

    /**
     * @observes salesrule_rule_save_before
     */
    public function salesruleSave()
    {
        try {
            Mage::helper('mxmallinone/sync')->setForceSync(
                Mxm_AllInOne_Helper_Sync::SYNC_TYPE_PROMOTION,
                true
            );
        } catch (Exception $e) {
            // Ensure the page still loads no matter what goes wrong here
        }
    }

    /**
     * @observes store_save_before
     */
    public function storeSave()
    {
        try {
            Mage::helper('mxmallinone/sync')->setForceSync(
                Mxm_AllInOne_Helper_Sync::SYNC_TYPE_STORE,
                true
            );
        } catch (Exception $e) {
            // Ensure the page still loads no matter what goes wrong here
        }
    }

    /**
     * @observes admin_system_config_changed_section_design
     * @observes admin_system_config_changed_section_trans_email
     * @observes admin_system_config_changed_section_general
     */
    public function storeConfigChange()
    {
        try {
            Mage::helper('mxmallinone/sync')->setForceSync(
                Mxm_AllInOne_Helper_Sync::SYNC_TYPE_STORE,
                true
            );
        } catch (Exception $e) {
            // Ensure the page still loads no matter what goes wrong here
        }
    }

    /**
     * @observes catalog_product_save_after
     */
    public function productSave($observer)
    {
        try {
            $product = $observer->getProduct();
            if ($product->getIsChangedCategories()) {
                Mage::helper('mxmallinone/sync')->setForceSync(
                    Mxm_AllInOne_Helper_Sync::SYNC_TYPE_CATEGORY_PRODUCT,
                    true
                );
            }
        } catch (Exception $e) {
            // Ensure the page still loads no matter what goes wrong here
        }
    }
}
