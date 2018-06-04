<?php

namespace Iksula\Checkoutcustomization\Plugin\Checkout\Model\Checkout;


class LayoutProcessor
{
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */

        protected $carriercodefactory;
    
    public function __construct(        
        \Iksula\Carriercodetelephone\Model\CarriercodedataFactory  $carriercodefactory
    )
    {    
        $this->carriercodefactory = $carriercodefactory;

    }

    public function getCountrycode() {
            $country_codeData = array();

            $country_code = $this->carriercodefactory->create()->getCollection()->addFieldToSelect('country_code');
            $country_code = $country_code->distinct(true);

            $html = array();
            $html [] = array('label' => 'Country code','value'=>'');
            foreach($country_code as $iCollectionsCode){

                $html [] = array('label' => $iCollectionsCode['country_code'] , 'value' => $iCollectionsCode['country_code']);
                            
            }

            // print_r($html);
            // exit();

            return $html;
    
    }

    public function getCarriercode() {
            $country_codeData = array();

            $country_code = $this->carriercodefactory->create()->getCollection()->addFieldToSelect('carrier_code');

            $country_code = $country_code->distinct(true);
            $html = array();
            $html [] = array('label' => 'Carrier code' , 'value' => '');
            foreach($country_code as $iCollectionsCode){

                $html [] = array('label' => $iCollectionsCode['carrier_code'] , 'value' => $iCollectionsCode['carrier_code']);


            }
            return $html;
    }

    public function aroundProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, \Closure $proceed, $jsLayout)
    {
        $ret = $proceed($jsLayout);
        unset($ret['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']['cryozonic_stripe-form']['children']['form-fields']['children']['area']['config']['tooltip']);
        unset($ret['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']['free-form']['children']['form-fields']['children']['area']['config']['tooltip']);
        return $ret;
    }


    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {

        $carrierOption = $this->getCarriercode();
        $countryOption = $this->getCountrycode();
        // array_unshift($countryOption , array('label' => 'country code','value'=>''));//;
        // print_r($countryOption);
        // exit;
        // array_unshift($arr , 'item1');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $Customer = $objectManager->get('\Magento\Customer\Model\Session'); 
        
        $customerID = $Customer->getCustomerId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerObj = $objectManager->create('Magento\Customer\Model\Customer')
        ->load($customerID);
        $customerfname = $customerObj->getFirstname();
        $customerlname = $customerObj->getLastname();
        $customerGender = $customerObj->getGender();
        $customerNationality = $customerObj->getNationality();
        $customerTelephone = $customerObj->getAccountTelephone();
                
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['firstname']['value'] = $customerfname;

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['lastname']['value'] = $customerlname;
        
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['value'] = $customerTelephone;

        // $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        // ['shippingAddress']['children']['shipping-address-fieldset']['children']['delivery_date'] = [
        //     'component' => 'Magento_Ui/js/form/element/abstract',
        //     'config' => [
        //         'customScope' => 'shippingAddress',
        //         'template' => 'ui/form/field',
        //         'elementTmpl' => 'ui/form/element/date',
        //         'options' => [],
        //         'id' => 'delivery-date'
        //     ],
        //     'dataScope' => 'shippingAddress.delivery_date',
        //     'label' => 'Delivery Date',
        //     'provider' => 'checkoutProvider',
        //     'visible' => true,
        //     // 'validation' => ['required-entry' => true],
        //     // 'sortOrder' => 5000,
        //     'id' => 'delivery-date'
        // ];

         $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['custom_area'] = [
            'component' => 'Magento_Ui/js/form/element/select',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                'options' => [],
                'id' => 'custom-area'
            ],
            'dataScope' => 'shippingAddress.custom_area',
            'label' => 'Area',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => ['required-entry' => true],
            'sortOrder' => 8,
            'id' => 'custom-area',
            'options' => [                
                [
                    'value' => '',
                    'label' => 'Area'
                ],               
            ]
        ];


        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['custom_telephone'] = [
         'component' => 'Magento_Ui/js/form/components/group',
            //'label' => __('Street Address'), I removed main label
            'required' => false, //turn false because I removed main label
            'dataScope' => 'shippingAddress',
            'provider' => 'checkoutProvider',
            'sortOrder' => 3,
            'type' => 'group',
            'additionalClasses' => 'custom_group',
            'children' => [
                [
                'component' => 'Magento_Ui/js/form/element/select',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/select',
                    'id' => 'country-code',
                ],
                'dataScope' => 'shippingAddress.country_code',
                'label' => 'Country Code',
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => ['required-entry' => true],
                // 'sortOrder' => 23,
                'id' => 'country-code',
                'options' => $countryOption
                // 'placeholder' => 'Carrier Code'
                
            ],
            [
            'component' => 'Magento_Ui/js/form/element/select',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                'id' => 'country-code',
            ],
            'dataScope' => 'shippingAddress.carrier_code',
            'label' => 'Carrier Code',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => ['required-entry' => true],
            // 'sortOrder' => 24,
            'id' => 'carrier-code',
            'options' => $carrierOption,
            'optionsCaption' => 'Carrier Code'
        ],[
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [],
                'id' => 'checkout_contact_no_config'
            ],
            'dataScope' => 'shippingAddress.contact_no',
            'label' => 'Contact No',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'data-bind' => true,
            'validation' => ['required-entry' => true,'validate-number'=>true,'mobile7digit'=>true],
            // 'sortOrder' => 30,
            'id' => 'contact_no',
            'placeholder' => 'Contact Number (Enter 7 digits)'                
        ]
        ]
        ];

        // $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        // ['shippingAddress']['children']['shipping-address-fieldset']['children']['country_code'] = [
        //     'component' => 'Magento_Ui/js/form/element/select',
        //     'config' => [
        //         'customScope' => 'shippingAddress',
        //         'template' => 'ui/form/field',
        //         'elementTmpl' => 'ui/form/element/select',
        //         'id' => 'country-code',
        //     ],
        //     'dataScope' => 'shippingAddress.country_code',
        //     'label' => 'Country Code',
        //     'provider' => 'checkoutProvider',
        //     'visible' => true,
        //     'validation' => [],
        //     // 'sortOrder' => 23,
        //     'id' => 'country-code',
        //     // 'options' => "$configCarriercode"
        //     'options' => [                
        //         [
        //             'value' => '97',
        //             'label' => '97',
        //         ],
        //         [
        //             'value' => '89',
        //             'label' => '89',
        //         ],
        //         [
        //             'value' => '101',
        //             'label' => '101',
        //         ]
        //     ]
        // ];

        //  $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        // ['shippingAddress']['children']['shipping-address-fieldset']['children']['carrier_code'] = [
        //     'component' => 'Magento_Ui/js/form/element/select',
        //     'config' => [
        //         'customScope' => 'shippingAddress',
        //         'template' => 'ui/form/field',
        //         'elementTmpl' => 'ui/form/element/select',
        //         'id' => 'country-code',
        //     ],
        //     'dataScope' => 'shippingAddress.carrier_code',
        //     'label' => 'Carrier Code',
        //     'provider' => 'checkoutProvider',
        //     'visible' => true,
        //     'validation' => [],
        //     // 'sortOrder' => 24,
        //     'id' => 'carrier-code',
        //     'options' => [                
        //         [
        //             'value' => '470',
        //             'label' => '470',
        //         ],
        //         [
        //             'value' => '579',
        //             'label' => '579',
        //         ],
        //         [
        //             'value' => '783',
        //             'label' => '783',
        //         ],
        //         [
        //             'value' => '981',
        //             'label' => '981',
        //         ]

        //     ]
        // ];
        
         // $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
         //    ['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['children'][1]['label'] = "Area";
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['sortOrder'] = 5;
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['country_id']['sortOrder'] = 6;
            // $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            // ['shippingAddress']['children']['shipping-address-fieldset']['children']['city']['sortOrder'] = 7;

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['region']['sortOrder'] = 8;

        // $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        // ['shippingAddress']['children']['shipping-address-fieldset']['children']['contact_no'] = [
        //     'component' => 'Magento_Ui/js/form/element/abstract',
        //     'config' => [
        //         'customScope' => 'shippingAddress',
        //         'template' => 'ui/form/field',
        //         'elementTmpl' => 'ui/form/element/input',
        //         'options' => [],
        //         'id' => 'checkout_contact_no_config'
        //     ],
        //     'dataScope' => 'shippingAddress.contact_no',
        //     'label' => 'Contact No',
        //     'provider' => 'checkoutProvider',
        //     'visible' => true,
        //     'data-bind' => true,
        //     'validation' => ['required-entry' => true,'validate-number'=>true,'mobile7digit'=>true],
        //     // 'sortOrder' => 30,
        //     'id' => 'contact_no'                
        // ];

        //placeholder
    $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
       ['shippingAddress']['children']['shipping-address-fieldset']['children']['firstname']['placeholder'] = __('First Name');
    $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
       ['shippingAddress']['children']['shipping-address-fieldset']['children']['lastname']['placeholder'] = __('Last Name');

    //$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
           // ['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['children'][0]['placeholder'] = __('Street Address');
    $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['street'] = [
        'component' => 'Magento_Ui/js/form/components/group',
        //'label' => __('Street Address'),
        'required' => true,
        'dataScope' => 'shippingAddress.street',
        'provider' => 'checkoutProvider',
        //'sortOrder' => 60,
        'type' => 'group',
        'additionalClasses' => 'street',
        'children' => [
                [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input'
                ],
                'dataScope' => '0',
                'provider' => 'checkoutProvider',
                'validation' => ['required-entry' => true, 'max_text_length'=>40, 'validate-customstreet'=> true],
                'placeholder' => 'Street Address' 
            ]
            //     [
            //     'component' => 'Magento_Ui/js/form/element/abstract',
            //     'config' => [
            //         'customScope' => 'shippingAddress',
            //         'template' => 'ui/form/field',
            //         'elementTmpl' => 'ui/form/element/input'
            //     ],
            //     'dataScope' => '1',
            //     'provider' => 'checkoutProvider',
            //     'validation' => ['required-entry' => false, "min_text_len‌​gth" => 1, "max_text_length" => 255],
            // ]
        ]
    ];
    // $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
    //    ['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['children'][0]['placeholder'] = __('Address');  
        // $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        //    ['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['children'][1]['placeholder'] = __('Area');
        // $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        //    ['shippingAddress']['children']['shipping-address-fieldset']['children']['delivery_date']['placeholder'] = __('Delivery Date');   

        //required validation changes   
        // $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        // ['shippingAddress']['children']['shipping-address-fieldset']['children']['delivery_date']['validation'] =
        // ['required-entry' => true];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['postcode']['validation'] =
        ['required-entry' => false]; 
              
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['city']['validation'] =
        ['required-entry' => false];
        
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['delivery_date']['validation'] =
        ['required-entry' => false];

         $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['validation'] =
        ['required-entry' => false];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['area']['validation'] =
        ['required-entry' => false];
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['addresss']['validation'] =
        ['required-entry' => false];
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['address_gender']['validation'] =
        ['required-entry' => false];

        return $jsLayout;
    }
}