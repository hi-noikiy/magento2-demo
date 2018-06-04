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
                template: 'Magento_OfflinePayments/payment/checkmo'
            },
            isVisible: agreementsConfig.isEnabled,

            /** Returns send check to info */
            getMailingAddress: function() {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },

            showtermandconditionscheckmo: function(){
                $('#checkout-agreements-modal-checkmo').css('display','block');
            },
            hidetermandconditionscheckmo: function(){
                $('#checkout-agreements-modal-checkmo').css('display','none');
            },

            /** Returns payable to info */
            getPayableTo: function() {
                return window.checkoutConfig.payment.checkmo.payableTo;
            }
        });
    }
);
