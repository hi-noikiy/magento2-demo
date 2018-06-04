/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'uiComponent',
        'ko',
        'jquery',
        'Magento_Checkout/js/model/sidebar'
    ],
    function(Component, ko, $, sidebarModel) {
        'use strict';

        var checkoutConfig = window.checkoutConfig;  
        var cart_note = checkoutConfig.cart_note;
        
        return Component.extend({
            cart_note: ko.observable(cart_note),
            cart_note_label: ko.observable("Note : "),

            setModalElement: function(element) {
                sidebarModel.setPopup($(element));
            }
        });
    }
);
