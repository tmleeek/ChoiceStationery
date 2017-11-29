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


if (Mage::helper('mstcore')->isModuleInstalled('Mana_Sorting') && class_exists('Mana_Sorting_Rewrite_Toolbar')) {
	class Mirasvit_Seo_Block_Catalog_Product_List_Toolbar_Adapter extends Mana_Sorting_Rewrite_Toolbar {
    }
} elseif (Mage::helper('mstcore')->isModuleInstalled('Iceshop_Icecatlive') && class_exists('Iceshop_Icecatlive_Block_Product_List_Toolbar')) {
    class Mirasvit_Seo_Block_Catalog_Product_List_Toolbar_Adapter extends Iceshop_Icecatlive_Block_Product_List_Toolbar {
    }
} elseif (Mage::helper('mstcore')->isModuleInstalled('Mirasvit_SeoFilter') && class_exists('Mirasvit_SeoFilter_Block_Catalog_Product_List_Toolbar')) {
    class Mirasvit_Seo_Block_Catalog_Product_List_Toolbar_Adapter extends Mirasvit_SeoFilter_Block_Catalog_Product_List_Toolbar {
    }
} else {
    class Mirasvit_Seo_Block_Catalog_Product_List_Toolbar_Adapter extends Mirasvit_Seo_Block_Catalog_Product_List_Toolbar {
    }
}

