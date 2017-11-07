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

class Idev_OneStepCheckout_Model_Source_Geoip
{
    public function toOptionArray()
    {
        $options = array(
            array('label'=>'Please choose GeoIp detection method', 'value'=>''),
            array('label'=>'GeoIP2 database', 'value'=>'geoip2_db'),
            array('label'=>'GeoIP2 online', 'value'=>'geoip2_online'),
            array('label'=>'Pear Net/GeoIp (legacy)', 'value'=>'pear_geoip'),
            array('label'=>'Apache mod_geoip', 'value'=>'mod_geoip'),
            array('label'=>'Pecl geoip', 'value'=>'pecl_geoip')
        );

        return $options;
    }
}
