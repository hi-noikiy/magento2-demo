<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksula\Networkonlinepayment\Model;



/**
 * Pay In Store payment method model
 */
class Network extends \Magento\Payment\Model\Method\AbstractMethod
{


    const MARCHANTKEY = 'payment/network/merchantKey';
    const MARCHANTID = 'payment/network/merchantId';

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'network';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;
    
    protected $_networkOnlineObject;
    
    protected $_scopeConfig;




    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Iksula\Networkonlinepayment\Helper\Network $helper,
        \Iksula\Networkonlinepayment\Helper\NetworkonlieBitmapPaymentIntegration $networkOnlineObject,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Checkout\Model\Session $checkoutSession      
              
    ) {
        $this->helper = $helper;
        $this->orderSender = $orderSender;
        $this->httpClientFactory = $httpClientFactory;
        $this->checkoutSession = $checkoutSession;
        $this->_networkOnlineObject = $networkOnlineObject;
        $this->_scopeConfig = $networkOnlineObject;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger
        );

    }

    public function getMarchantKey() {
     $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
     return $this->_scopeConfig->getValue(self::MARCHANTKEY, $storeScope); //you get your value here
    }

    public function getMarchantId() {
     $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
     return $this->_scopeConfig->getValue(self::MARCHANTID, $storeScope); //you get your value here
    }


    public function getRedirectUrl() {
        return $this->helper->getUrl($this->getConfigData('redirect_url'));
    }

    public function getReturnUrl() {
        return $this->helper->getUrl($this->getConfigData('return_url'));
    }

    public function getCancelUrl() {
        return $this->helper->getUrl($this->getConfigData('cancel_url'));
    }

    public function getCgiUrl() {
        $env = $this->getConfigData('environment');
        if ($env === 'production') {
            return $this->getConfigData('production_url');
        }
        return $this->getConfigData('sandbox_url');
    }

    public function buildCheckoutRequest() {
        $order = $this->checkoutSession->getLastRealOrder();

        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $order1 = $objectManager->create('\Magento\Sales\Model\Order') ->load($order->getData('increment_id'));
        $order->setState("pending_payment")->setStatus('pending_payment');
        $order->save();

        $billing_address = $order->getBillingAddress();
        $customer = $order->getCustomerId();

        $pronamestring = "";
        $propricestring = "";
        foreach($order->getAllItems() as $item){
              $ProdustIds[]= $item->getProductId();

              $pronamestring .= $item->getName().","; // product name
              $propricestring .= $item->getPrice().","; // product name
        }



        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $currencysymbol = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currency = $currencysymbol->getStore()->getCurrentCurrencyCode();

        // echo "aaaa".$this->getMarchantKey();
        // echo "aaaa".$this->getMarchantId();
        // exit();
        $params_firstname = $billing_address->getFirstName();
        $params_lastname = $billing_address->getLastname();
        $params_city = $billing_address->getCity();
        $params_street = $billing_address->getData('street');
        $params_state = $billing_address->getRegion();
        $params_zip = $billing_address->getPostcode();
        $params_country = $billing_address->getCountryId();
        $params_email = $order->getCustomerEmail();
        $params_phone = $billing_address->getTelephone();

        $telcode = explode('-', $params_phone);
        $telcode1 = $telcode[0];
        $telcode2 = $telcode[1];
        $telcode3 = $telcode[2];

        $networkOnlineArray = array('Network_Online_setting' => array(
                                            'merchantKey'    => $this->getMarchantKey(),            // Your key provided by network international
                                            'merchantId'     => $this->getMarchantId(), //  Your merchant ID ex: 201408191000001
                                            'collaboratorId' => 'NI',                // Constant used by Network Online international
                                            'iv'             => '0123456789abcdef', // Used for initializing CBC encryption mode
                                            'url'            => false              // Set to false if you are using testing environment , set to true if you are using live environment
                                ),
                                'Block_Existence_Indicator' => array(
                                            'transactionDataBlock' => true,
                                            'billingDataBlock'     => true,
                                            'shippingDataBlock'    => true,
                                            'paymentDataBlock'     => false,
                                            'merchantDataBlock'    => false,
                                            'otherDataBlock'       => false,
                                            'DCCDataBlock'         => false
                                ),
                                'Field_Existence_Indicator_Transaction' => array(
                                            'merchantOrderNumber'  =>  $order->getData('increment_id'), 
                                            'amount'               => round($order->getBaseGrandTotal(), 0),
                                            'successUrl'           => $this->getReturnUrl(),
                                            'failureUrl'           => $this->getReturnUrl(),
                                            'transactionMode'      => 'INTERNET',
                                            'payModeType'          => '',
                                            'transactionType'      => '01',
                                            'currency'             => $currency
                                ),
                                'Field_Existence_Indicator_Billing' => array(
                                           'billToFirstName'       => $params_firstname, 
                                            'billToLastName'        => $params_lastname,
                                            'billToStreet1'         => $params_street,
                                            'billToStreet2'         => '',
                                            'billToCity'            => $params_city,
                                            'billToState'           => $params_state,
                                            'billtoPostalCode'      => '000000',
                                            'billToCountry'         => $params_country,
                                            'billToEmail'           => $params_email,
                                            'billToMobileNumber'    => '',
                                            'billToPhoneNumber1'    => $telcode1,
                                            'billToPhoneNumber2'    => $telcode2,
                                            'billToPhoneNumber3'    => $telcode3
                                ),
                                'Field_Existence_Indicator_Shipping' => array(
                                             'shipToFirstName'    => $params_firstname, 
                                            'shipToLastName'     => $params_lastname,
                                            'shipToStreet1'      => $params_street,
                                            'shipToStreet2'      => '', 
                                            'shipToCity'         => $params_city,
                                            'shipToState'        => $params_state,
                                            'shipToPostalCode'   => '000000',
                                            'shipToCountry'      => $params_country,
                                            'shipToPhoneNumber1' => $telcode1,
                                            'shipToPhoneNumber2' => $telcode2,
                                            'shipToPhoneNumber3' => $telcode3,
                                            'shipToMobileNumber' => ''
                                ),
                                'Field_Existence_Indicator_Payment' => array(
                                            'cardNumber'      => '', // 1. Card Number  
                                            'expMonth'        => '',                 // 2. Expiry Month 
                                            'expYear'         => '',             // 3. Expiry Year
                                            'CVV'             => '',              // 4. CVV  
                                            'cardHolderName'  => '',          // 5. Card Holder Name 
                                            'cardType'        => '',             // 6. Card Type
                                            'custMobileNumber'=> '',       // 7. Customer Mobile Number
                                            'paymentID'       => '',           // 8. Payment ID 
                                            'OTP'             => '',           // 9. OTP field 
                                            'gatewayID'       => '',             // 10.Gateway ID 
                                            'cardToken'       => ''              // 11.Card Token 
                                ),
                                'Field_Existence_Indicator_Merchant'  => array(
                                                    'UDF1'   => '115.121.181.112', // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
                                                    'UDF2'   => 'abc',             // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
                                                    'UDF3'   => 'abc',             // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
                                                    'UDF4'   => 'abc',             // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
                                                    'UDF5'   => 'abc',             // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
                                                    'UDF6'   => 'abc',             // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
                                                    'UDF7'   => 'abc',             // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
                                                    'UDF8'   => 'abc',             // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
                                                    'UDF9'   => 'abc',             // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
                                                    'UDF10'  => 'abc'              // This is a ‘user-defined field’ that can be used to send additional information about the transaction.                             
                                ),
                                'Field_Existence_Indicator_OtherData'  => array(
                                        'custID'                 => $customer,  
                                        'transactionSource'      => 'IVR',                      
                                        'productInfo'            => $pronamestring,                         
                                        'isUserLoggedIn'         => 'Y',                            
                                        'itemTotal'              => $propricestring, 
                                        'itemCategory'           => '',                         
                                        'ignoreValidationResult' => 'FALSE'
                                ),
                                'Field_Existence_Indicator_DCC'   => array(
                                        'DCCReferenceNumber' => '09898787', // DCC Reference Number
                                        'foreignAmount'      => '240.00', // Foreign Amount
                                        'ForeignCurrency'    => 'USD'  // Foreign Currency
                                )
                            );

        $params = $this->_networkOnlineObject->customEncryption($networkOnlineArray);

        return $params;

    }   

    
}
