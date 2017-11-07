<?php /* added automatically by conflict fixing tool */ if (Mage::getConfig()->getNode('modules/Ophirah_Qquoteadv/active')) {
                class Rewardpoints_Block_Adminhtml_Customer_Edit_Tabs_Amasty_Pure extends Ophirah_Qquoteadv_Block_Adminhtml_Customer_Edit_Tabs {}
            } else if (Mage::getConfig()->getNode('modules/Amasty_List/active')) {
                class Rewardpoints_Block_Adminhtml_Customer_Edit_Tabs_Amasty_Pure extends Amasty_List_Block_Adminhtml_Customer_Edit_Tabs {}
            } else { class Rewardpoints_Block_Adminhtml_Customer_Edit_Tabs_Amasty_Pure extends Mage_Adminhtml_Block_Customer_Edit_Tabs {} } ?>