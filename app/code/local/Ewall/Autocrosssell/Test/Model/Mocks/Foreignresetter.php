<?php
class Ewall_Autocrosssell_Test_Model_Mocks_Foreignresetter extends Mage_Core_Model_Abstract {
    public static $counter = 0;
    public static function dropForeignKeys() {
        if (!self::$counter) {
            $resource = Mage::getModel('core/resource');
            $connection = $resource->getConnection('core_write');
            $FKscope = array(
                'autocrosssell' => array('FK_WBTAB_INT_STORE_ID')
            );

            foreach ($FKscope as $table => $fks) {
                foreach ($fks as $fk) {
                    try {
                        $connection->exec(new Zend_Db_Expr("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk}`"));
                        $connection->exec(new Zend_Db_Expr("ALTER TABLE `{$table}` DROP KEY `{$fk}`"));
                    } catch (Exception $e) {
                        
                    }
                }
            }


            self::$counter = 1;
        }
    }

}
