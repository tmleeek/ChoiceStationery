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
 * @package   mirasvit/extension_seoautolink
 * @version   1.0.14
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_SeoAutolink_Helper_Migration
{
   /**
     * @param Mage_Core_Model_Resource_Setup $installer
     * @param string                         $table
     * @param string                         $columnName
     * @param string                         $columnType
     */
    public function addColumn($installer, $table, $columnName, $columnType)
    {
        $this->trySql($installer, "ALTER TABLE $table ADD $columnName $columnType");
    }

    /**
     * @param Mage_Core_Model_Resource_Setup $installer
     * @param string                         $sql
     */
    public function trySql($installer, $sql)
    {
        try {
            $installer->run($sql);
        } catch (Exception $e) {
            //            throw $e;
        }
    }
}