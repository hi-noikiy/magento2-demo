/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        "underscore",
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/step-navigator',
        'jquery/jquery.hashchange',
        'Magento_Customer/js/action/login',
        'Magento_Customer/js/model/customer'
    ],
    function ($, _, ko, Component, stepNavigator,loginAction,customer) {
        var steps = stepNavigator.steps;

        return Component.extend({isCustomerLoginRequired: checkoutConfig.isCustomerLoginRequired,
            defaults: {
                template: 'Magento_Checkout/progress-bar',
                visible: true
            },
            isCustomerLoggedIn: customer.isLoggedIn,
            steps: steps,

            initialize: function() {
                this._super();
                $(window).hashchange(_.bind(stepNavigator.handleHash, stepNavigator));
                stepNavigator.handleHash();

                // jQuery(window).bind("load",function(){
                //     jQuery(".opc-progress-bar li").each(function(){   
                //         var nthDiv = jQuery(this).index();
                //         // console.log("aa"+nthDiv);
                //         console.log("aa"+nthDiv);
                //         if( jQuery(this).hasClass('_complete')){
                //             jQuery('.opc > .step-title').eq(nthDiv).addClass('complete');
                //         }
                //         if( jQuery(this).hasClass('_active')){
                //             jQuery('.opc > .step-title').eq(nthDiv).addClass('active');
                //         }
                //     });
                        
                //     });

                    // jQuery(".shipping-title").on('click',function(){   
                    //     jQuery(".opc-progress-bar li").each(function(){   
                    //         // console.log("hi");
                    //     var nthDiv = jQuery(this).index();
                    //     // console.log(nthDiv);
                    //         if( jQuery(this).hasClass('_complete')){
                    //             jQuery('.opc > .step-title').eq(nthDiv).addClass('complete');
                    //         }
                    //         if( jQuery(this).hasClass('_active')){
                    //             jQuery('.opc > .step-title').eq(nthDiv).addClass('active');
                    //         }
                    //     });    
                    //     }); 
                    var url      = window.location.href;
                    if(isCustomerLoggedIn && url.indexOf("shipping") <= 0 && url.indexOf("payment") <= 0 && url.indexOf("step_code") <= 0  ){
                         $('.opc > .step-title').eq(1).addClass('active');                              

                    }

                if(isCustomerLoggedIn){

                    if (url.indexOf("shipping") >= 0){
                         $('.opc > .step-title').eq(1).addClass('active');                              
                    }
                    if (url.indexOf("payment") >= 0){
                         $('.opc > .step-title').eq(2).addClass('active');                                                                                 
                    }
                    $(document).on('click','.shipping-title',function(){
                        // $(".opc-progress-bar li:nth-child(2) span").trigger("click");
                        $('.opc > .step-title').eq(1).addClass('active');
                        $('.opc > .step-title').eq(2).removeClass('active');
                    });
                }else{

                    if (url.indexOf("step_code") >= 0){
                         $('.opc > .step-title').eq(0).addClass('active');                              
                    }
                }



                    jQuery(document).ajaxComplete(function(){
                    jQuery(".opc-progress-bar li").each(function(){   
                        // console.log("hi");
                    var nthDiv = jQuery(this).index();
                    // console.log(nthDiv);
                        if( jQuery(this).hasClass('_complete')){
                            jQuery('.opc > .step-title').eq(nthDiv).addClass('complete');
                        }
                        if( jQuery(this).hasClass('_active')){
                            jQuery('.opc > .step-title').eq(nthDiv).addClass('active');
                        }else{
                            jQuery('.opc > .step-title').eq(nthDiv).removeClass('active');                            
                        }
                    });    
                });
            },

            sortItems: function(itemOne, itemTwo) {
                return stepNavigator.sortItems(itemOne, itemTwo);
            },

            navigateTo: function(step) {
                stepNavigator.navigateTo(step.code);
            },

            isProcessed: function(item) {
                return stepNavigator.isProcessed(item.code);
            }
        });
    }
);
