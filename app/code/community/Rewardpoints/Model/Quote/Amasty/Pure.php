<?php /* added automatically by conflict fixing tool */ if (Mage::getConfig()->getNode('modules/Idev_OneStepCheckout/active')) {
                class Rewardpoints_Model_Quote_Amasty_Pure extends Idev_OneStepCheckout_Model_Sales_Quote {}
            } else if (Mage::getConfig()->getNode('modules/Amasty_Coupons/active')) {
                class Rewardpoints_Model_Quote_Amasty_Pure extends Amasty_Coupons_Model_Sales_Quote {}
            } else { class Rewardpoints_Model_Quote_Amasty_Pure extends Idev_OneStepCheckout_Model_Sales_Quote {} } ?>