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


class Mirasvit_Seo_Helper_Snippets_Price extends Mage_Core_Helper_Abstract
{
    /**
     * Format snippets price to match Google guidelines
     * @param string $price
     * @return string
     */
    public function formatPriceValue($price) {
        if ($price) {
            if (substr_count($price, ',') + substr_count($price, '.') > 1) {
                // if 6,451,00 or 6.451.00 --> 6451.00
                if (substr_count($price, ',') == 2 || substr_count($price, '.') == 2) {
                    $price = str_replace(',', '.', $price);
                    $price = preg_replace('/\./', '', $price, 1);
                }
                // if 6,451.00 --> 6451.00
                elseif (strpos($price, ',') < strpos($price, '.')) {
                    $price = str_replace(',', '', $price);
                // if 6.451,00 --> 6451.00
                } elseif (strpos($price, ',') > strpos($price, '.')) {
                    $price = str_replace('.', '', $price);
                    $price = str_replace(',', '.', $price);
                }
                // if 3,99 --> 3.99
            } elseif (strpos($price, ',') !== false) {
                $price = str_replace(',', '.', $price);
            }

            return $price;
        }

        return false;
    }
}