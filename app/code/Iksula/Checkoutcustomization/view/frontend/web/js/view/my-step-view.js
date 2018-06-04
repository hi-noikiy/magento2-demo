define(
    [
        'ko',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'jquery',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/url',
        'Magento_Ui/js/form/form',
        'Magento_Customer/js/action/login',
        'Magento_Customer/js/model/customer',
        'mage/validation',
        'Magento_Checkout/js/model/authentication-messages',
        'Magento_Checkout/js/model/payment/renderer-list',
        'mage/storage'
    ],
    function (
        ko,
        _,
        stepNavigator,
        $,
        fullScreenLoader,
        urlBuilder,
        Component,
        loginAction,
        customer,
        validation,
        messageContainer,
        rendererList,
        storage
    ) {
        'use strict';
        var checkoutConfig = window.checkoutConfig;
        var country_code = checkoutConfig.country_code;
        var carrier_code = checkoutConfig.carrier_code;
        /**
        *
        * mystep - is the name of the component's .html template,
        * <Vendor>_<Module>  - is the name of the your module directory.
        *
        */
        return Component.extend({
            isGuestCheckoutAllowed: checkoutConfig.isGuestCheckoutAllowed,
            isCustomerLoginRequired: checkoutConfig.isCustomerLoginRequired,
            registerUrl: checkoutConfig.registerUrl,
            forgotPasswordUrl: checkoutConfig.forgotPasswordUrl,
            autocomplete: checkoutConfig.autocomplete,
            defaults: {
                template: 'Iksula_Checkoutcustomization/mystep'
            },

            //add here your logic to display step,
            isVisible: ko.observable(true),
            isChecked: ko.observable(false),
            loginusername: ko.observable(""),
            loginpassword: ko.observable(""),
            isCustomerLoggedIn: customer.isLoggedIn,
            isVisible: ko.observable(!isCustomerLoggedIn),
            /**
            *
            * @returns {*}
            */
            initialize: function () {
                this._super();
                this.rememberme();
                $('.opc > .step-title').eq(0).addClass('active');

                // register your step
                stepNavigator.registerStep(
                    //step code will be used as step content id in the component template
                    'step_code',
                    //step alias
                    null,
                    //step title value
                    'Login',
                    //observable property with logic when display step or hide step
                    this.isVisible,

                    _.bind(this.navigate, this),

                    /**
                    * sort order value
                    * 'sort order value' < 10: step displays before shipping step;
                    * 10 < 'sort order value' < 20 : step displays between shipping and payment step
                    * 'sort order value' > 20 : step displays after payment step
                    */
                    0
                );
                // custome changes for
                if(!isCustomerLoggedIn){
                    this.isVisible(true);
                    window.location = window.checkoutConfig.checkoutUrl + "#step_code";
                }


                return this;
            },

            /**
            * The navigate() method is responsible for navigation between checkout step
            * during checkout. You can add custom logic, for example some conditions
            * for switching to your custom step
            */
            navigate: function () {
                if(!isCustomerLoggedIn){
                    this.isVisible(true);
                    window.location = window.checkoutConfig.checkoutUrl + "#step_code";
                }else{
                    this.isVisible(false);
                    window.location = window.checkoutConfig.checkoutUrl + "#shipping";
                }
            },

            rememberme : function(){
                var remember = $.cookie("remember");
                if(remember!=null){
                    var rememberobject = jQuery.parseJSON(remember);
                    var username = rememberobject.username;
                    var password = rememberobject.password;
                    var rememberme = rememberobject.remchkbox;
                    if(username!=""){
                        this.loginusername(username);
                    }
                    if(password!=""){
                        this.loginpassword(password);
                    }
                    if(rememberme==1){
                        this.isChecked(true);
                    }
                }
            },

            validateForm: function (form) {
                return jQuery(form).validation() && jQuery(form).validation('isValid');
            },

            /**
            * @returns void
            */
            navigateToNextStep: function (loginForm) {
                var loginData = {},
                formDataArray = $(loginForm).serializeArray();

                $(".checkout-login-error").html("");
                $(".checkout-register-error").hide();
                $(".checkout-register-error").html("");
                $(".checkout-login-error").hide();

                formDataArray.forEach(function (entry) {
                    loginData[entry.name] = entry.value;
                });

                if($(loginForm).validation() && $(loginForm).validation('isValid')){
                    fullScreenLoader.startLoader();
                    loginAction(loginData, checkoutConfig.checkoutUrl, undefined, messageContainer).always(function(response) {
                        console.log(response);
                        if(response.errors == 'false'){
                            fullScreenLoader.stopLoader();
                            stepNavigator.next();
                            $('.opc > .step-title').eq(1).addClass('active');
                        }
                        else{
                            fullScreenLoader.stopLoader();
                            $(".checkout-login-error").html(response.message);
                            $(".checkout-login-error").show();
                        }
                    });
                }
            },
            facebookCall: function () {
                $(".btn-facebook").trigger("click");
            },
            googleCall: function () {
                $(".btn-google").trigger("click");

            },
            createAccount : function(){
                // $(".register-wrapper-form").show();
                // $('select[name=register-countrycode]').append(country_code);
                // $('select[name=register-carriercode]').append(carrier_code);
                var ajax_url = urlBuilder.build('checkoutcustomization/register/form');
               window.location.href = ajax_url;
            },
            closeAccount : function(){
                $(".register-wrapper-form").hide();
            },
            navigateRegisterToNextStep: function (registerUrl) {

                $(".checkout-register-error").html("");
                $(".checkout-register-error").hide();

                if (this.validateForm('#checkout-register-form')) {
                    var firstname = $("input[name=register-firstname]").val();
                    var lastname = $("input[name=register-lastname]").val();
                    var email = $("input[name=register-email]").val();
                    var password = $("input[name=register-password]").val();
                    var confirmpassword = $("input[name=register-confirmpassword]").val();
                    var countrycode = $("select[name=register-countrycode]").val();
                    var carriercode = $("select[name=register-carriercode]").val();
                    var contactno = $("input[name=register-contactno]").val();
                    var nationality = $("select[name=register-nationality]").val();
                    var gender = $('input[name=gender]:checked').val();
                    if($("input[name=is_subscribed]").is(':checked')){
                        var is_subscribed_val = 1;
                    }else{
                        var is_subscribed_val = 0;
                    }
                    var is_subscribed = $("input[name=is_subscribed]").val();
                    var ajax_url = urlBuilder.build('checkoutcustomization/ajax/registration');

                    $.ajax({
                        url: ajax_url,
                        data: {firstname :firstname,
                                lastname :lastname,
                                email :email,
                                password :password,
                                password_confirmation :confirmpassword,
                                country_code :countrycode,
                                carrier_code :carriercode,
                                tele_number :contactno,
                                nationality :nationality,
                                is_subscribed :is_subscribed_val,
                                gender :gender
                        },
                        type: 'post',
                        context: this,
                    }).done(
                        function (response) {
                            console.log(response);
                            if(response.errors == false){
                                fullScreenLoader.stopLoader();
                                    stepNavigator.next();
                                     location.reload();
                                    // window.location = window.checkoutConfig.checkoutUrl+"#shipping";
                            }
                            else{
                                fullScreenLoader.stopLoader();
                                $(".checkout-register-error").html(response.message);
                                $(".checkout-register-error").show();
                            }
                        }
                    ).fail(
                        function (response) {
                            fullScreenLoader.stopLoader();
                            console.log(response);
                        }
                    );
                }

            }
        });
    }
);
