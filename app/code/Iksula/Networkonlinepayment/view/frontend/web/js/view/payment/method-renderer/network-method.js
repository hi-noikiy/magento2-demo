define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Iksula_Networkonlinepayment/js/action/set-payment-method',
    ],
    function (Component,setPaymentMethod) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Iksula_Networkonlinepayment/payment/network'
            },

            redirectAfterPlaceOrder: false,
        
            afterPlaceOrder: function () {
                setPaymentMethod();    
            },

            /** Returns send check to info */
            /*getMailingAddress: function() {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },*/

           
        });
    }
);
