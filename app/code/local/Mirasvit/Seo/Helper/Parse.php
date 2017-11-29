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


class Mirasvit_Seo_Helper_Parse extends Mage_Core_Helper_Abstract
{
    //e.g. of str
    //[product_name][, model: {product_model}!] [product_nonexists]  [buy it {product_nonexists} !]
    public function parse($str, $objects, $additional = array(), $storeId = false)
    {
        if (trim($str) == '') {
            return null;
        }

        $b1Open  = '[ZZZZZ';
        $b1Close = 'ZZZZZ]';
        $b2Open  = '{WWWWW';
        $b2Close = 'WWWWW}';

        $str = str_replace('[', $b1Open, $str);
        $str = str_replace(']', $b1Close, $str);
        $str = str_replace('{', $b2Open, $str);
        $str = str_replace('}', $b2Close, $str);

        $pattern = '/\[ZZZZZ[^ZZZZZ\]]*ZZZZZ\]/';

        preg_match_all($pattern, $str, $matches, PREG_SET_ORDER);

        $vars = array();
        foreach ($matches as $matche) {
            $vars[$matche[0]] = $matche[0];
        }

        foreach ($objects as $key => $object) {
            $data = $object->getData();
            if (isset($additional[$key])) {
                $data = array_merge($data, $additional[$key]);
            }

            foreach ($data as $dataKey => $value) {
                if (is_array($value) || is_object($value)) {
                    continue;
                }

                $k1   = $b2Open.$key.'_'.$dataKey.$b2Close;
                $k2   = $b1Open.$key.'_'.$dataKey.$b1Close;
                $skip = true;

                foreach ($vars as $k =>$v) {
                    if (stripos($v, $k1) !== false || stripos($v, $k2) !== false) {
                        $skip = false;
                        break;
                    }
                }

                if ($skip) {
                    continue;
                }

                $value = $this->checkForConvert($object, $key, $dataKey, $value, $storeId);
                foreach ($vars as $k =>$v) {
                    if ($value == '') {
                        if (stripos($v, $k1) !== false || stripos($v, $k2) !== false) {
                            $vars[$k] = '';
                            continue;
                        }
                    }

                    $v = str_replace($k1, $value, $v);
                    $v = str_replace($k2, $value, $v);
                    $vars[$k] = $v;
                }
            }
        }

        foreach ($vars as $k => $v) {
            //if no attibute like [product_nonexists]
            if ($v == $k) {
                $v = '';
            }

            //remove start and end symbols from the string (trim)
            if (substr($v, 0, strlen($b1Open)) == $b1Open) {
                    $v = substr($v, strlen($b1Open), strlen($v));
            }

            if (strpos($v, $b1Close) === strlen($v)-strlen($b1Close)) {
                $v = substr($v, 0, strlen($v)-strlen($b1Close));
            }

            //if no attibute like [buy it {product_nonexists} !]
            if (stripos($v, $b2Open) !== false || stripos($v, $b1Open) !== false) {
                $v = '';
            }

            $str = str_replace($k, $v, $str);
        }

        return $str;
    }

    protected function checkForConvert($object, $key, $dataKey, $value, $storeId)
    {
        if ($key == 'product' || $key == 'category') {
            if ($key == 'product') {
                $attribute = Mage::getSingleton('catalog/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $dataKey);
            } else {
                $attribute = Mage::getSingleton('catalog/config')->getAttribute(Mage_Catalog_Model_Category::ENTITY, $dataKey);
            }

            if ($storeId) {
                $attribute->setStoreId($storeId);
            }

            if ($attribute->getId() > 0) {
                try {
                    $valueId = $object->getDataUsingMethod($dataKey);
                    $value = $attribute->getFrontend()->getValue($object);
                    /*second variant, may be needed for specific stores - begin */
                    // $value = $attribute->getFrontend()->getValue($object);
                    // if ($key == 'product') {
                    //     $attributeType = $attribute->getFrontendInput();
                    //     if ($attributeType == 'multiselect' || $attributeType == 'select') {
                    //         $value = $object->getAttributeText($dataKey);
                    //     } else {
                    //         $value = Mage::getResourceModel('catalog/product')->getAttributeRawValue($object->getId(), $dataKey, $storeId);
                    //     }
                    // } else {
                    //     $value = $attribute->getFrontend()->getValue($object);
                    // }
                    /*second variant, may be needed for specific stores - end */
                } catch(Exception $e) {//possible that some extension is removed, but we have it attribute with source in database
                    $value = '';
                }

                if (!$value) { //need for manufacturer
                    try {
                        $value = $object->getResource()->getAttribute($dataKey)->getFrontend()->getValue($object);
                    } catch(Exception $e) {
                        $value = '';
                    }
                }

                // To avoid displaying "No4G available" for [{product_4G} available] if a yes/no attribute is "No"
                if ((strtolower($value) == 'no'
                    || strtolower($value) == 'nein'
                    || strtolower($value) == 'nie'
                    || strtolower($value) == 'não'
                    || strtolower($value) == 'không') 
                    && $valueId == '') {
                    $value = '';
                }

                // To display " 4G available" instead of "Yes4G available" for [{product_4G} available] if a yes/no attribute is "Yes"
                if (strtolower($value) == 'yes'
                    || strtolower($value) == 'ja') {
                    $value = ' ';
                }

                switch ($dataKey) {
                    case 'price':
                        $value = Mage::helper('core')->currency($value, true, false);
                        break;
                    case 'special_price':
                        $value = Mage::helper('core')->currency($value, true, false);
                        break;
                }
            } else {
                switch ($dataKey) {
                    case 'final_price':
                        $value = Mage::helper('core')->currency($value, true, false);
                        break;
                }
            }
        }

        if (is_array($value)) {
           if (isset($value['label'])) {
               $value = $value['label'];
           } else {
               $value = '';
           }
        }

        return $value;
    }

}
