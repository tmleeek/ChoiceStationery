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
 * @package    AW_Advancedreports
 * @version    2.7.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Advancedreports_Model_System_Config_Source_Frequency
{
    const DAILY_OPTION_VALUE = 0;
    const WEEKLY_OPTION_VALUE = 1;
    const MONTHLY_OPTION_VALUE = 2;

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::DAILY_OPTION_VALUE,
                'label' => Mage::helper('advancedreports')->__('Daily')
            ),
            array(
                'value' => self::WEEKLY_OPTION_VALUE,
                'label' => Mage::helper('advancedreports')->__('Weekly')
            ),
            array(
                'value' => self::MONTHLY_OPTION_VALUE,
                'label' => Mage::helper('advancedreports')->__('Monthly')
            ),
        );
    }

    static public function toOptionHash()
    {
        return array(
            self::DAILY_OPTION_VALUE  => Mage::helper('advancedreports')->__('daily'),
            self::WEEKLY_OPTION_VALUE => Mage::helper('advancedreports')->__('weekly'),
            self::MONTHLY_OPTION_VALUE => Mage::helper('advancedreports')->__('monthly')
        );
    }
}