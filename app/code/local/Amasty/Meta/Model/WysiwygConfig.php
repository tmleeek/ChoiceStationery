<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
 */
class Amasty_Meta_Model_WysiwygConfig extends Mage_Cms_Model_Wysiwyg_Config
{
    public function getConfig($data = array())
    {
        $adminUrl = Mage::getSingleton('adminhtml/url');
        $request = $adminUrl->getRequest();

        $oldName = $request->getRouteName();

        $request->setRouteName('adminhtml');
        $config = parent::getConfig($data);
        $request->setRouteName($oldName);

        return $config;
    }
}
