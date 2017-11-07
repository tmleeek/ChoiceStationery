<?php /* added automatically by conflict fixing tool */ if (Mage::getConfig()->getNode('modules/Amasty_Coupons/active')) {
                class Idev_OneStepCheckout_Model_Sales_Quote_Amasty_Pure extends Amasty_Coupons_Model_Sales_Quote {}
            } else { class Idev_OneStepCheckout_Model_Sales_Quote_Amasty_Pure extends Mage_Sales_Model_Quote {} } ?>