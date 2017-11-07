<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Brands
 */

class Amasty_Brands_Block_Search extends Mage_Core_Block_Template
{
    /**
     * @return string
     * @throws Mage_Core_Exception
     */
    public function getBrands()
    {
        $brandCollection = Mage::getModel('ambrands/brand')
            ->getCollection()
            ->addFieldToFilter('is_active', '1')
            ->addAttributeToSelect('name');
        $res = array();
        foreach ($brandCollection as $brand) {
            $url = $brand->getUrl();
            $res[$url] = $brand->getName();
        }
        return json_encode($res);
    }
}