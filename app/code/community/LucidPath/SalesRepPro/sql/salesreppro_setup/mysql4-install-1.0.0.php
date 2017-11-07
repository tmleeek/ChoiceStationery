<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('admin/user'), 'salesrep_commission_rate', 'DECIMAL(5,2)');

if (!$installer->tableExists($installer->getTable('salesrep'))) {
$installer->run("
CREATE TABLE IF NOT EXISTS `{$installer->getTable('salesrep')}` (
  salesrep_id int(10) unsigned NOT NULL auto_increment,
  order_id int(10) unsigned not null,
  rep_id int(10),
  rep_name varchar(250),
  rep_commission_earned DECIMAL(12,2),
  rep_commission_status varchar(50) default 'unpaid',
  PRIMARY KEY(salesrep_id),
  CONSTRAINT `FK_SALESREP_ORDER` FOREIGN KEY (`order_id`) REFERENCES `{$installer->getTable('sales/order')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
} else {
  $installer->getConnection()->addColumn($installer->getTable('salesrep'), 'rep_commission_earned', 'DECIMAL(12,2)');
  $installer->getConnection()->addColumn($installer->getTable('salesrep'), 'rep_commission_status', "varchar(50) default 'unpaid'");
}

$installer->setConfigData('salesrep/email_reports/email_template', 'salesrep_email_reports_email_template');

$installer->endSetup();
?>
