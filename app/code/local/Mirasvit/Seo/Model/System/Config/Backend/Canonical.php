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


class Mirasvit_Seo_Model_System_Config_Backend_Canonical extends Mage_Core_Model_Config_Data
{
    protected function _afterSave()
    {
        if($this->getValue()) {
            $config = Mage::getSingleton('core/config');
            $config->saveConfig( Mage_Catalog_Helper_Category::XML_PATH_USE_CATEGORY_CANONICAL_TAG, 0, $this->getScope() , $this->getScopeId());
            $config->saveConfig( Mage_Catalog_Helper_Product::XML_PATH_USE_PRODUCT_CANONICAL_TAG, 0, $this->getScope() , $this->getScopeId());
        }
    }
}
