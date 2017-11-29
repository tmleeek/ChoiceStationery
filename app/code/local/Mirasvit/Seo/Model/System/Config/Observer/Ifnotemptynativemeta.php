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


class Mirasvit_Seo_Model_System_Config_Observer_Ifnotemptynativemeta extends Varien_Object
{
	public function getConfig()
    {
        return Mage::getSingleton('seo/config');
    }

    /**
     * Adds Notice Message when "Use meta tags from categories if they are not empty"
     * or/and "Use meta tags from products if they are not empty" option is being enabled
     */
	public function useMetaTagsFromMagentoIfNotEmptyChange($e)
    {
        $controllreAction = $e->getEvent()->getControllerAction();
        if (!$controllreAction) {
            return;
        }

        $params = $controllreAction->getRequest()->getParams();

        if (isset($params['section']) 
            && $params['section'] == 'seo'
            && isset($params['groups']['general']['fields']['is_category_meta_tags_used']['value'])) {
                if ($params['groups']['general']['fields']['is_category_meta_tags_used']['value'] == 1
                    && $this->getConfig()->isCategoryMetaTagsUsed() != $params['groups']['general']['fields']['is_category_meta_tags_used']['value']) {
                    $message = '"Use meta tags from categories if they are not empty" option is set to "Yes". '
                    .'SEO values from the category General Information tab prevail over the category SEO Templates';
                    Mage::getSingleton('adminhtml/session')->addNotice($message);
                }
        }

        if (isset($params['section']) 
            && $params['section'] == 'seo'
            && isset($params['groups']['general']['fields']['is_product_meta_tags_used']['value'])) {
                if ($params['groups']['general']['fields']['is_product_meta_tags_used']['value'] == 1
                    && $this->getConfig()->isProductMetaTagsUsed() != $params['groups']['general']['fields']['is_product_meta_tags_used']['value']) {
                    $message = '"Use meta tags from products if they are not empty" is set to "Yes". '
                    .'SEO values from the product Meta Information tab prevail over product SEO Templates';
                    Mage::getSingleton('adminhtml/session')->addNotice($message);
                }
        }
    }
}