<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Sphinx Search Ultimate
 * @version   2.3.1
 * @revision  601
 * @copyright Copyright (C) 2013 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_MstCore_Helper_Data extends Mage_Core_Helper_Data
{
    public function isModuleInstalled($modulename)
    {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;

        if(isset($modulesArray[$modulename]) && $modulesArray[$modulename]->is('active')) {
            return true;
        } else {
            return false;
        }
    }

    public function pr($arr, $ip = false, $die = false)
    {
        if (!$ip) {
            pr($arr);
        } elseif ($_SERVER['REMOTE_ADDR'] == $ip) {
            pr($arr);
            if ($die) {
                die();
            }
        }
    }
}

if (!function_exists('pr')) {
    
    function pr($arr)
    {
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }
}
