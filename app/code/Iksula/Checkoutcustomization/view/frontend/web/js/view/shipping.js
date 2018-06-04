/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'underscore',
        'Magento_Ui/js/form/form',
        'ko',
        'mage/url', 
        'Magento_Checkout/js/model/shipping-rate-processor/customer-address',
        'Magento_Checkout/js/model/shipping-rate-processor/new-address',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'uiRegistry',
        'mage/translate',
        'Magento_Checkout/js/model/shipping-rate-service'
    ],
    function (
        $,
        _,
        Component,
        ko,
        urlBuilder,
        customerAddressProcessor,
        newAddressProcessor,
        customer,
        addressList,
        addressConverter,
        quote,
        createShippingAddress,
        selectShippingAddress,
        shippingRatesValidator,
        formPopUpState,
        shippingService,
        selectShippingMethodAction,
        rateRegistry,
        setShippingInformationAction,
        stepNavigator,
        modal,
        checkoutDataResolver,
        checkoutData,
        registry,
        $t
    ) {
        'use strict';
            var region_id = "";
            var ajax_url = urlBuilder.build('checkoutcustomization/getarea/index');
               $.ajax({
                        url: ajax_url,
                        data: {region_id :region_id},
                        type: 'post',
                        context: this,
                    }).done(
                        function (response) {
                           if(response.errors===true){
                                jQuery("select[name='country_id'] > option:first").remove();
                                jQuery("select[name='country_id'] > option:first").remove();
                                jQuery("select[name='country_id']").prepend('<option value=""> Please select Country</option>');
                                jQuery("select[name='shippingAddress[country_code]'] > option:first").remove();
                                jQuery("select[name='shippingAddress[carrier_code]'] > option:first").remove();
                            }
                        }
                    ).fail(
                        function (response) {
                            console.log(response);
                        }
                    );




        var checkoutConfig = window.checkoutConfig;
        var  startdate = parseInt(checkoutConfig.delivery_date_start_date);
        var dayscount = parseInt(checkoutConfig.delivery_date_days_count);
        var country_code = checkoutConfig.country_code;
        var carrier_code = checkoutConfig.carrier_code;

        function customLoadShippingMethod(){
            var regionId = $('select[name="region_id"]  option:selected').val();
            var countryId = $('select[name="country_id"]  option:selected').val();
            var region = $('select[name="region_id"]  option:selected').text();
            if(countryId.length != 0 && region.length != 0 && regionId.length != 0)
            {
                var address = quote.shippingAddress();
                address.countryId = countryId;
                address.region =region ;
                address.regionId = regionId;
                address.regionCode = '';
                //address.trigger_reload = new Date().getTime();
                //rateRegistry.set(address.getKey(), null);
                //rateRegistry.set(address.getCacheKey(), null);
                quote.shippingAddress(address);
            }
        }

        try{customLoadShippingMethod()}catch(e){};

        $(document).on('change', 'select[name="region_id"]', function(){
            var region_id = this.value;
            var address = quote.shippingAddress();
            // clearing cached rates to retrieve new ones
            rateRegistry.set(address.getCacheKey(), null);
            var type = quote.shippingAddress().getType();
            console.log(type);
            if (type!="new-customer-address") {
                customerAddressProcessor.getRates(address);
            } else {
                newAddressProcessor.getRates(address);
            }


            var ajax_url = urlBuilder.build('checkoutcustomization/getarea/index');
               $.ajax({
                        url: ajax_url,
                        data: {region_id :region_id},
                        type: 'post',
                        context: this,
                    }).done(
                        function (response) {
                           // if(response.errors===true){
                           //      // jQuery("select[name='country_id'] > option:first").remove();
                           //      // jQuery("select[name='shippingAddress[country_code]'] > option:first").remove();
                           //      // jQuery("select[name='shippingAddress[carrier_code]'] > option:first").remove();
                           //  }
                           var region_id = this.value;
                            var address = quote.shippingAddress();
                            // clearing cached rates to retrieve new ones
                            rateRegistry.set(address.getCacheKey(), null);
                            var type = quote.shippingAddress().getType();
                            console.log(type);
                            if (type!="new-customer-address") {
                                customerAddressProcessor.getRates(address);
                            } else {
                                newAddressProcessor.getRates(address);
                            }
                                
                            if(response.errors===false){
                                $('select[name="custom_area"]').empty().append(response.optionsvalue);
                            }

                            try{customLoadShippingMethod()}catch(e){};
                           // customLoadShippingMethod();


                        }
                    ).fail(
                        function (response) {
                            console.log(response);
                        }
                    );




        });

        // $(document).on('change', 'select[name="custom_area"]', function(){
        //         alert()

        // });




        var popUp = null;

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping'
            },
            visible: ko.observable(!quote.isVirtual()),
            errorValidationMessage: ko.observable(false),
            isCustomerLoggedIn: customer.isLoggedIn,
            isFormPopUpVisible: formPopUpState.isVisible,
            isFormInline: addressList().length == 0,
            isNewAddressAdded: ko.observable(false),
            postcode: ko.observable("00000"),
            saveInAddressBook: 1,
            quoteIsVirtual: quote.isVirtual(),

            /**
             * @return {exports}
             */
            initialize: function () {
                var self = this,
                    hasNewAddress,
                    fieldsetName = 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset';


                this._super();

                if (!quote.isVirtual()) {
                    stepNavigator.registerStep(
                        'shipping',
                        '',
                        $t('Shipping'),
                        this.visible, _.bind(this.navigate, this),
                        10
                    );
                }
                checkoutDataResolver.resolveShippingAddress();

                hasNewAddress = addressList.some(function (address) {
                    return address.getType() == 'new-customer-address';
                });

                this.isNewAddressAdded(hasNewAddress);

                this.isFormPopUpVisible.subscribe(function (value) {
                    if (value) {
                        self.getPopUp().openModal();
                    }
                });

                quote.shippingMethod.subscribe(function () {
                    self.errorValidationMessage(false);
                });

                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var shippingAddressData = checkoutData.getShippingAddressFromData();

                    if (shippingAddressData) {
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                    }
                    checkoutProvider.on('shippingAddress', function (shippingAddressData) {
                        checkoutData.setShippingAddressFromData(shippingAddressData);
                    });
                    shippingRatesValidator.initFields(fieldsetName);
                });




                ko.bindingHandlers.datepicker = {
                            after: ['attr'],
                            init: function(element, valueAccessor, allBindingsAccessor) {
                                var $el = $(element);


                                var tomorrow = new Date();
                                tomorrow.setDate(tomorrow.getDate() + startdate);

                                var next_fifteen = new Date();
                                next_fifteen.setDate(next_fifteen.getDate() + startdate + dayscount);

                                /*console.log(tomorrow);
                                console.log(next_fifteen);*/

                        //initialize datepicker with some optional options
                        var options = { minDate: tomorrow,
                            maxDate: next_fifteen,
                            dateFormat: 'dd-mm-yy',
                        };

                        $el.datepicker(options);

                        //handle the field changing
                        ko.utils.registerEventHandler(element, "change", function () {
                            self.gettimings($(element).attr('data-item-id'));
                        });

                        var writable = valueAccessor();
                        if (!ko.isObservable(writable)) {
                            var propWriters = allBindingsAccessor()._ko_property_writers;
                            if (propWriters && propWriters.datepicker) {
                                writable = propWriters.datepicker;
                            } else {
                                return;
                            }
                        }
                        writable($(element).datepicker("getDate"));

                    },
                    update: function(element, valueAccessor)   {
                        var widget = $(element).data("DateTimePicker");
                        //when the view model is updated, update the widget
                        if (widget) {
                            var date = ko.utils.unwrapObservable(valueAccessor());
                            widget.date(date);
                        }
                    }
                };

        return this;
            },

            /**
             * Load data from server for shipping step
             */
            navigate: function () {
                //load data from server for shipping step
            },


            /**
             * @return {*}
             */
            getPopUp: function () {
                var self = this,
                    buttons;

                if (!popUp) {
                    buttons = this.popUpForm.options.buttons;
                    this.popUpForm.options.buttons = [
                        {
                            text: buttons.save.text ? buttons.save.text : $t('Save and deliver here'),
                            class: buttons.save.class ? buttons.save.class : 'action primary action-save-address',
                            click: self.saveNewAddress.bind(self)
                        },
                        {
                            text: buttons.cancel.text ? buttons.cancel.text : $t('Cancel'),
                            class: buttons.cancel.class ? buttons.cancel.class : 'action secondary action-hide-popup',

                            /** @inheritdoc */
                            click: this.onClosePopUp.bind(this)
                        }
                    ];
                    this.popUpForm.options.closed = function () {
                        self.isFormPopUpVisible(false);
                    };

                    this.popUpForm.options.modalCloseBtnHandler = this.onClosePopUp.bind(this);
                    this.popUpForm.options.keyEventHandlers = {
                        escapeKey: this.onClosePopUp.bind(this)
                    };

                    /** @inheritdoc */
                    this.popUpForm.options.opened = function () {
                        // Store temporary address for revert action in case when user click cancel action
                        self.temporaryAddress = $.extend(true, {}, checkoutData.getShippingAddressFromData());
                    };
                    popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
                }

                return popUp;
            },

            /**
             * Revert address and close modal.
             */
            onClosePopUp: function () {
                checkoutData.setShippingAddressFromData($.extend(true, {}, this.temporaryAddress));
                this.getPopUp().closeModal();
            },

            /**
             * Show address form popup
             */
            showFormPopUp: function () {
                $('select[name=country_code]').append(country_code);
                $('select[name=carrier_code]').append(carrier_code);
                this.isFormPopUpVisible(true);
            },

            /**
             * Save new shipping address
             */
            saveNewAddress: function () {
                var addressData,
                    newShippingAddress;

                this.source.set('params.invalid', false);
                this.source.trigger('shippingAddress.data.validate');

                if (!this.source.get('params.invalid')) {
                    addressData = this.source.get('shippingAddress');
                    // if user clicked the checkbox, its value is true or false. Need to convert.
                    addressData.save_in_address_book = this.saveInAddressBook ? 1 : 0;

                    // console.log(addressData);
                    // New address must be selected as a shipping address
                    // console.log(addressData);
                    // var array = $.map(addressData, function(value, index) {
                    //     return [value];
                    // });

                    console.log(addressData);
                    var carrier_code1 = addressData.shippingAddress.carrier_code;
                    var country_code1 = addressData.shippingAddress.country_code;
                    var contact_no = addressData.shippingAddress.contact_no;
                    var area = addressData.custom_area;

                    var telephone = country_code1+"-"+carrier_code1+"-"+contact_no;
                    addressData.telephone =telephone;
                    addressData.city =area;
                    addressData.postcode ="00000";

                    // console.log(addressData);


                    newShippingAddress = createShippingAddress(addressData);
                    selectShippingAddress(newShippingAddress);
                    checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                    checkoutData.setNewCustomerShippingAddress($.extend(true, {}, addressData));
                    this.getPopUp().closeModal();
                    this.isNewAddressAdded(true);
                }
            },

            /**
             * Shipping Method View
             */
            rates: shippingService.getShippingRates(),
            isLoading: shippingService.isLoading,
            isSelected: ko.computed(function () {
                    return quote.shippingMethod() ?
                        quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code
                        : null;
                }
            ),

            /**
             * @param {Object} shippingMethod
             * @return {Boolean}
             */
            selectShippingMethod: function (shippingMethod) {
                selectShippingMethodAction(shippingMethod);
                checkoutData.setSelectedShippingRate(shippingMethod.carrier_code + '_' + shippingMethod.method_code);

                return true;
            },

            /**
             * Set shipping information handler
             */
            setShippingInformation: function () {
                if (this.validateShippingInformation()) {
                    setShippingInformationAction().done(
                        function () {
                            stepNavigator.next();
                        }
                    );
                }
            },

            /**
             * @return {Boolean}
             */
            validateShippingInformation: function () {
                var shippingAddress,
                    addressData,
                    loginFormSelector = 'form[data-role=email-with-possible-login]',
                    emailValidationResult = customer.isLoggedIn();

                if (!quote.shippingMethod()) {
                    this.errorValidationMessage('Please specify a shipping method.');

                    return false;
                }

                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }

                if (this.isFormInline) {
                    this.source.set('params.invalid', false);
                    this.source.trigger('shippingAddress.data.validate');

                    if (this.source.get('shippingAddress.custom_attributes')) {
                        this.source.trigger('shippingAddress.custom_attributes.data.validate');
                    }

                    if (this.source.get('params.invalid') ||
                        !quote.shippingMethod().method_code ||
                        !quote.shippingMethod().carrier_code ||
                        !emailValidationResult
                    ) {
                        return false;
                    }

                    shippingAddress = quote.shippingAddress();
                    addressData = addressConverter.formAddressDataToQuoteAddress(
                        this.source.get('shippingAddress')
                    );
                    var tempAddress = this.source.get('shippingAddress');


                    console.log(addressData);
                    var carrier_code1 = tempAddress.shippingAddress.carrier_code;
                    var country_code1 = tempAddress.shippingAddress.country_code;
                    var contact_no = tempAddress.shippingAddress.contact_no;
                    var area = tempAddress.custom_area;

                    var telephone = country_code1+"-"+carrier_code1+"-"+contact_no;
                    addressData.telephone =telephone;
                    addressData.city =area;
                    addressData.postcode ="000000";
                    console.log(addressData);

                    //Copy form data to quote shipping address object
                    for (var field in addressData) {

                        if (addressData.hasOwnProperty(field) &&
                            shippingAddress.hasOwnProperty(field) &&
                            typeof addressData[field] != 'function' &&
                            _.isEqual(shippingAddress[field], addressData[field])
                        ) {
                            shippingAddress[field] = addressData[field];
                        } else if (typeof addressData[field] != 'function' &&
                            !_.isEqual(shippingAddress[field], addressData[field])) {
                            shippingAddress = addressData;
                            break;
                        }
                    }

                    if (customer.isLoggedIn()) {
                        shippingAddress.save_in_address_book = 1;
                    }
                    selectShippingAddress(shippingAddress);
                }

                if (!emailValidationResult) {
                    $(loginFormSelector + ' input[name=username]').focus();

                    return false;
                }

                return true;
            }
        });
    }
);
