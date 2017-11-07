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
 * @package    AW_Ajaxcartpro
 * @version    3.2.13
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

try {
    $this->startSetup();
    if ($this->getTable('admin/permission_block')) {
        $this->getConnection()->insertMultiple(
            $this->getTable('admin/permission_block'),
            array(
                array('block_name' => 'ajaxcartpro/confirmation_items_continue', 'is_allowed' => 1),
                array('block_name' => 'ajaxcartpro/confirmation_items_gotocheckout', 'is_allowed' => 1),
            )
        );
    }
    $this->endSetup();
} catch (Exception $e) {
    Mage::logException($e);
}