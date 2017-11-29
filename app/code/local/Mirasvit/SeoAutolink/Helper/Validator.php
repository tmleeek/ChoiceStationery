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


class Mirasvit_SeoAutolink_Helper_Validator extends Mirasvit_MstCore_Helper_Validator_Abstract
{
    public function testMagentoCrc()
    {
        $filter = array(
            'app/code/core/Mage/Core',
            'app/code/core/Mage/Review',
            'js'
        );

        return Mage::helper('mstcore/validator_crc')->testMagentoCrc($filter);
    }

    public function testMirasvitCrc()
    {
        $modules = array('SeoAutolink');
        return Mage::helper('mstcore/validator_crc')->testMirasvitCrc($modules);
    }

    public function testTablesExists()
    {
        $result = self::SUCCESS;
        $title = 'SeoAutolink: Required tables exist';
        $description = array();

        $tables = array(
            'core/store',
            'seoautolink/link',
            'seoautolink/link_store',
        );

        foreach ($tables as $table) {
            if (!$this->dbTableExists($table)) {
                $description[] = "Table '$table' not exists";
                $result = self::FAILED;
            }
        }

        return array($result, $title, $description);
    }

    public function testColumnsExists()
    {
        $result = self::SUCCESS;
        $title = 'SeoAutolink: Required columns exist';
        $description = array();
        $tableName = 'seoautolink/link';
        $fullTableName = Mage::getSingleton('core/resource')->getTableName($tableName);
        $tableColumns = array('url_title', 'sort_order');

        foreach ($tableColumns as $column) {
            if (!$this->dbTableColumnExists($tableName, $column)) {
                $description[] = "Column '$column' does not exist in table '$fullTableName'";
                $result = self::FAILED;
            }
        }

        return array($result, $title, $description);
    }

}