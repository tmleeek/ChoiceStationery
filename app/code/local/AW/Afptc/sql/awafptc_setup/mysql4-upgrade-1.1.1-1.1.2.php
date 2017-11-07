<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Afptc
 * @version    1.1.12
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

$installer = $this;

$ruleCollection = Mage::getResourceModel('awafptc/rule_collection');
$updateConditionList = array();
foreach ($ruleCollection as $ruleModel) {
    $conditions = unserialize($ruleModel->getData('conditions_serialized'));
    if ($conditions['type'] == 'salesrule/rule_condition_combine') {
        $conditions['type'] = 'awafptc/rule_condition_combine';
    }
    if (array_key_exists('conditions', $conditions) && is_array($conditions['conditions'])) {
        foreach ($conditions['conditions'] as $conditionId => $condition) {
            if ($condition['type'] == 'salesrule/rule_condition_product_subselect') {
                $conditions['conditions'][$conditionId]['type'] = 'awafptc/rule_condition_product_subselect';
            }
        }
    }
    $updateConditionList[$ruleModel->getId()] = serialize($conditions);
}

$preparedSqlPartList = array();
foreach ($updateConditionList as $ruleId => $serializedConditions) {
    $preparedSqlPartList[] = "SET `conditions_serialized` = '{$serializedConditions}' WHERE `rule_id` = {$ruleId}";
}

if (count($preparedSqlPartList)> 0) {
    $this->startSetup();
    foreach ($preparedSqlPartList as $preparedSql) {
        $this->run("
            UPDATE {$this->getTable('awafptc/rule')} {$preparedSql};
        ");
    }
    $installer->endSetup();
}