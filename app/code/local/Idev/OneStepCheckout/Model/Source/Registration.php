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

class Idev_OneStepCheckout_Model_Source_Registration
{
    public function toOptionArray()
    {
        $options = array(
        array('label'=>'Require registration/login', 'value'=>'require_registration'),
        array('label'=>'Disable registration/login', 'value'=>'disable_registration'),
        array('label'=>'Allow guests and logged in users', 'value'=>'allow_guest'),
        array('label'=>'Enable registration on success page', 'value'=>'registration_success'),
        array('label'=>'Auto-generate account for new emails', 'value'=>'auto_generate_account'),
        );

        return $options;
    }
}
