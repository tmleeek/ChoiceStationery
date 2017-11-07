<?php
/**
 * OneStepCheckout
 * 
 * NOTICE OF LICENSE
 *
 * This source file is subject to One Step Checkout AS software license.
 *
 * License is available through the world-wide-web at this URL:
 * https://www.onestepcheckout.com/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mail@onestepcheckout.com so we can send you a copy immediately.
 *
 * @category   Idev
 * @package    Idev_OneStepCheckout
 * @copyright  Copyright (c) 2009 OneStepCheckout  (https://www.onestepcheckout.com/)
 * @license    https://www.onestepcheckout.com/LICENSE.txt
 */

class Idev_OneStepCheckout_Block_Adminhtml_Intervals extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {

        $this->addColumn(
            'start', array(
                'label' => Mage::helper('onestepcheckout')->__('Start'),
                'style' => 'width:45px'
            )
        );
        $this->addColumn(
            'end', array(
                'label' => Mage::helper('onestepcheckout')->__('End'),
                'style' => 'width:45px'
            )
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('onestepcheckout')->__('Add Interval');

        parent::__construct();

    }

}
