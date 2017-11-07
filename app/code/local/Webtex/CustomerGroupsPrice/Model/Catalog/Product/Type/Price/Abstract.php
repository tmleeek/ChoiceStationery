<?php

if ((string)Mage::getConfig()->getModuleConfig('MageWorx_CustomOptions')->active == 'true'){
    class Webtex_CustomerGroupsPrice_Model_Catalog_Product_Type_Price_Abstract extends MageWorx_CustomOptions_Model_Catalog_Product_Type_Price {}
} else {
    class Webtex_CustomerGroupsPrice_Model_Catalog_Product_Type_Price_Abstract extends Mage_Catalog_Model_Product_Type_Price {}
}