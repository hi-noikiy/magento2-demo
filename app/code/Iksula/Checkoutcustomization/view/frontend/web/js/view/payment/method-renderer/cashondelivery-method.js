/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_CheckoutAgreements/js/model/agreements-modal'
    ],
    function (ko, $,Component,agreementsModal) {
        'use strict';
         var checkoutConfig = window.checkoutConfig,
            agreementManualMode = 1,
            agreementsConfig = checkoutConfig ? checkoutConfig.checkoutAgreements : {};
        return Component.extend({
            defaults: {
                template: 'Magento_OfflinePayments/payment/cashondelivery'
            },
            isVisible: agreementsConfig.isEnabled,

            /** Returns payment method instructions */

            showtermandconditionscash: function(){
                $('#checkout-agreements-modal-cashondelivery').css('display','block');
            },
            hidetermandconditionscash: function(){
                $('#checkout-agreements-modal-cashondelivery').css('display','none');
            },
            getInstructions: function() {
                return window.checkoutConfig.payment.instructions[this.item.method];
            }
        });
    }
);
