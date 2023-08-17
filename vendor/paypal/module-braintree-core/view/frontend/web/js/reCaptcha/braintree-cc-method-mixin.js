/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Checkout/js/model/payment/additional-validators'
], function ($, additionalValidators) {
    'use strict';

    return function (originalComponent) {
        return originalComponent.extend({
            /**
             * Initializes reCaptcha
             */
            placeOrder: function () {
                var original = this._super.bind(this),
                    // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    isEnabled = window.checkoutConfig.recaptcha_braintree,
                    // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                    paymentFormSelector = $('#co-payment-form'),
                    startEvent = 'captcha:startExecute',
                    endEvent = 'captcha:endExecute';

                if (!additionalValidators.validate() || !isEnabled || this.getCode() !== 'braintree') {
                    return original();
                }

                paymentFormSelector.off(endEvent).on(endEvent, function () {
                    var recaptchaCheckBox = jQuery("#recaptcha-checkout-braintree-wrapper input[name='recaptcha-validate-']");

                    var recaptchaCheckBox2 = jQuery("#recaptcha-checkout-braintree-wrapper div#recaptcha-checkout-braintree");
                    
                    var recaptchaCheckBox3 = jQuery("#recaptcha-checkout-braintree-wrapper");
                    

                    if ((recaptchaCheckBox.length == 0) || (recaptchaCheckBox2.length == 0) || (recaptchaCheckBox3.length == 0) || (recaptchaCheckBox.prop('checked') === false)) {
                        alert('Please indicate google recaptcha');
                    } else {
                        original();
                        paymentFormSelector.off(endEvent);
                    }
                });

                paymentFormSelector.trigger(startEvent);
            }
        });
    };
});
