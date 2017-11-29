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
 * @package   mirasvit/extension_seositemap
 * @version   1.0.10
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


class Mirasvit_SeoSitemap_Model_Config_Source_Ignore_Cms_Pages
{
    public function toArray()
    {
        $collection = Mage::getModel('cms/page')->getCollection()
                             ->addFieldToFilter('is_active', true)
                             ;
        $result = array();
        foreach($collection as $v) {
            $result[$v['identifier']] = $v['title'];
        }
        return $result;
    }


    public function toOptionArray()
    {
        $result = array();
        foreach($this->toArray() as $k=>$v) {
            $result[] = array('value'=>$k, 'label'=>$v);
        }
        return $result;
    }

    /************************/
}