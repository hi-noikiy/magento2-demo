/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'uiComponent'
    ],
    function (Component) {
        "use strict";
         var quoteItemData = window.checkoutConfig.quoteItemData;
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/summary/item/details'
            },
            quoteItemData: quoteItemData,
            getItemFlavor: function(quoteItem) {
            var itemProduct = this.getItemProduct(quoteItem.item_id);
            return itemProduct.sku;
        	},
            getValue: function(quoteItem) {
                return quoteItem.name;
            }
        });
    }
);