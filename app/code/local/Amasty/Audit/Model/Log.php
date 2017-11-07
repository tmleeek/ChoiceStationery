<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Audit
 */
class Amasty_Audit_Model_Log extends Mage_Core_Model_Abstract
{
    public function _construct()
    {    
        $this->_init('amaudit/log', 'entity_id');
    }

    public function clearLog($fromObserver = true)
    {
        $tableLogName = Mage::getSingleton('core/resource')->getTableName('amaudit/log');
        $days = Mage::getStoreConfig('amaudit/log/delete_logs_afret_days');
        $query = "DELETE FROM `$tableLogName`";

        if ($fromObserver) {
            $query = $query . 'WHERE date_time < NOW() - INTERVAL ' . $days . ' DAY';
        }

        Mage::getSingleton('core/resource')
            ->getConnection('core_write')
            ->query($query)
        ;

    }
}