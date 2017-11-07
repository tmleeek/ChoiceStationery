<?php
/**
* @category Customer Version 1.5
* @package MageB2B_Sublogin
* @author AIRBYTES GmbH <info@airbytes.de>
* @copyright AIRBYTES GmbH
* @license commercial
* @date 26.04.2014
*/
$installer = $this;
$installer->startSetup();
// automatically set all old sublogins to expire_date+90 and active=1
$installer->run("UPDATE {$installer->getTable('customer_sublogin')} set expire_date =ADDDATE(CURDATE(), INTERVAL 90 DAY), active=1;");
