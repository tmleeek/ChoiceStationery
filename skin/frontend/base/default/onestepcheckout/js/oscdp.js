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

Date.prototype.sameDay = function() {
    return this.getFullYear().toString() + this.getMonth().toString() + this.getDate().toString();
}

var oscDeliverydate = Class
        .create(
            {
            initialize : function(target, config) {
                this.config = config;
                var target = $(target);
                if (target) {
                    var sMethods = $$('.onestepcheckout-shipping-method')
                            .first();

                    if (sMethods) {
                        sMethods.insert(
                            {
                            after : target
                            }
                        );
                    }

                    if(sMethods && config.enabled_for.length > 0){
                        var form = $('onestepcheckout-form');
                        var shipping_method = $RF(form, 'shipping_method');

                        if(config.enabled_for.indexOf(shipping_method) >= 0 ){
                            target.toggle();
                        }
                    } else {
                        target.toggle();
                    }

                    var opener = $('add_ddate');

                    if(opener){
                        opener.observe(
                            'click', function(){
                            var wrapper = $('ddatewrap');
                            if(wrapper){
                                wrapper.toggle();
                            }
                            }
                        );
                    }

                    var dateField = $('deliverydate:date');

                    if (dateField) {

                        var now = new Date();
                        var end = new Date(now);
                        end.setDate(now.getDate()+parseInt(config.end));

                        if(config.start){
                            var start = new Date(now);
                            var startfrom = [];
                            start.setDate(start.getDate());
                            startfrom.push(start.getFullYear().toString()+start.getMonth().toString()+start.getDate().toString());
                            for (i = 0; i < config.start-1; i++) {
                                start.setDate(start.getDate() + 1);
                                startfrom.push(start.getFullYear().toString()+start.getMonth().toString()+start.getDate().toString());
                            }
                        }

                        var weekdays =  config.weekdays;
                        var tempweek = Array.apply(null, Array(7)).map(Number.prototype.valueOf,7);
                        if(weekdays.length > 0){
                            weekdays.each(
                                function(e){
                                tempweek[e] = e;
                                }
                            );
                        }

                        var calendarSetup = {
                            inputField : "deliverydate:date",
                            ifFormat : "%d/%m/%Y",
                            daFormat : "%d/%m/%Y",
                            showsTime : false,
                            weekNumbers : false,
                            bottomBar : false,
                            fdow : 1,
                            fixed : true,
                            button : "datepicker:date",
                            align : "Bl",
                            singleClick : true,
                            disableFunc : function(date) {

                                //disable last year
                                if(date.getFullYear() < now.getFullYear()) {
                                    return true;
                                }

                                //disable previous months
                                if(date.getFullYear() == now.getFullYear()) {
                                    if(date.getMonth() < now.getMonth()) {
                                        return true;
                                    }
                                }

                                //disable previous days
                                if(date.getMonth() == now.getMonth()) {
                                    if(date.getDate() < now.getDate()) {
                                        return true;
                                    }
                                }

                              //disable next days
                                if(config.start > 0) {
                                    if(startfrom.indexOf(date.sameDay()) > -1) {
                                        return true;
                                    }
                                }

                                //disalbe weekends
                                if(tempweek.length > 0){
                                    if(date.getDay() != tempweek[date.getDay()]){
                                        return true;
                                    }
                                }

                                //disable future after
                                if(config.end > 0){
                                     if(date > end){
                                         return true;
                                     }
                                }

                                //disable all other disabled dates
                                date = date.print("%d/%m/%Y");
                                return date in config.excluded;
                            },
                            range: [(now.getFullYear()), (now.getFullYear()+1)]
                        }

                        Calendar.setup(calendarSetup);
                    }

                    // inject the post values to OneStepCheckout ajax requests
                    Ajax.Responders
                            .register(
                                {
                                onCreate : function(a) {
                                    if (a.url.endsWith('set_methods_separate/')
                                            || a.url.endsWith('save_billing/')) {
                                        var dvalues = $$('#ddate input, #ddate textarea, #ddate select');
                                        if (dvalues.length > 0) {
                                            dvalues
                                                    .each(
                                                        function(e) {
                                                        a.options.parameters[e.name] = e.value;
                                                        }
                                                    );
                                            a.options.postBody = Object
                                                    .toQueryString(a.options.parameters);
                                        }
                                    }
                                }
                                }
                            );

                }
            }
            }
        );
