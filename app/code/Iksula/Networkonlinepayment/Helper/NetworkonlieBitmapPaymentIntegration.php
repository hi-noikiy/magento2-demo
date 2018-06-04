<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksula\Networkonlinepayment\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order;
use Magento\Framework\App\Helper\AbstractHelper;

class NetworkonlieBitmapPaymentIntegration extends AbstractHelper
{

    //Define Encryption details
    public $encryptMethod =  MCRYPT_RIJNDAEL_128;
    public $encryptMode   =  MCRYPT_MODE_CBC;

    //Define the property values
    public $merchantId;
    public $merchantKey;
    public $iv;
    public $collaboratorId;
    public $url;
    public $blockExistenceIndicator;

    //Define the Block Existence Indicator for Bitmap

    public $transactionDataBlock = true;  // Transaction Data Block  ==> This is mandatory block  , 1
    public $billingDataBlock     = false; // Billing Data Block      ==> This is an optional block ,0
    public $shippingDataBlock    = false; // Shipping Data Block     ==> This is an optional block ,0
    public $paymentDataBlock     = false; // Payment Data Block      ==> This is mandatory block , 1 if you are using Merchant hosted Payment , else 0
    public $merchantDataBlock    = false; // Merchant Data Block     ==> This is an optional block ,0
    public $otherDataBlock       = false; // Other Details Data Block==> This is an optional block ,0
    public $DCCDataBlock         = false; // DCC Data Block          ==> This is an optional block ,0

    // Total Seven blocks so the Block Existence Indicator Bitmap will be : 1001000
    // If you are selecting the Billing Data Block the Bitmap indicator will be : 1101000
    // If you are selecting the Shipping Data Block the Bitmap indicator will be :1111000

    // Define the Field Existence Indicator for the Transaction Data Block , All the fields are mandatory *
    public $merchantOrderNumber  = ''; // Merchant Order Number :  Unique identifier of the merchant , Like order id , cart id , pay id etc.. As of now we are passing unique timestamp
    public $amount               = ''; // Amount
    public $successUrl           = ''; // Add your sucess URL
    public $failureUrl           = ''; // Add your failure URL
    public $transactionMode      = ''; // Transaction Mode  ( Internet, Moto, or Recurring) Default Value Internet
    public $payModeType          = ''; // (CC)-Credit Card, (DC)-Debit Card, (DD)-Direct Debit, (PAYPAL)-PayPal, (NB)-Net Banking . Note: This field is required for ‘Merchant Hosted’ type of integration.
    public $transactionType      = ''; // 01 for SALE 02 for AUTHORIZATION
    public $currency             = 'AED'; // AED  Used to specify the currency for the transaction. Currently, only AED is accepted for all transactions (as per ISO Currency Code).

    // Define the Field Existence Indicator for the Billing Data Block , These are optional values
    public $billToFirstName     = '';  // 1. BillToFirstName Optional value
    public $billToLastName      = '';  // 2. BillToLastName  Optional value
    public $billToStreet1       = '';  // 3. BillToStreet1   Optional value
    public $billToStreet2       = '';  // 4. BillToStreet2   Optional value
    public $billToCity          = '';  // 5. BillToCity      Optional value
    public $billToState         = '';  // 6. BillToState     Optional value
    public $billtoPostalCode    = '';  // 7. BillToPostalCode Optional value
    public $billToCountry       = '';  // 8. BillToCountry   Optional value
    public $billToEmail         = '';  // 9. BillToEmailID   Optional value
    public $billToMobileNumber  = '';  // 10. BillToMobileNumber Optional value
    public $billToPhoneNumber1  = '';  // 11. BillToPhoneNumber1 Optional value
    public $billToPhoneNumber2  = '';  // 12. BillToPhoneNumber2 Optional value
    public $billToPhoneNumber3  = '';  // 13. BillToPhoneNumber2 Optional value

    // Define the Field Existence Indicator for the Shipping Data Block , These are optional values
    public $shipToFirstName     = ''; // 1. ShipToFirstName Optional value
    public $shipToLastName      = ''; // 2. ShipToLastName Optional value
    public $shipToStreet1       = ''; // 3. ShipToStreet1 Optional value
    public $shipToStreet2       = ''; // 4. ShipToStreet2 Optional value
    public $shipToCity          = ''; // 5. ShipToCity Optional value
    public $shipToState         = ''; // 6. ShipToState Optional value
    public $shipToPostalCode    = ''; // 7. ShipToPostalCode Optional value
    public $shipToCountry       = ''; // 8. ShipToCountry Optional value
    public $shipToPhoneNumber1  = ''; // 9. ShipToPhoneNumber1 Optional value
    public $shipToPhoneNumber2  = ''; // 10. ShipToPhoneNumber2 Optional value
    public $shipToPhoneNumber3  = ''; // 11. ShipToPhoneNumber3 Optional value
    public $shipToMobileNumber  = ''; // 12. ShipToMobileNumber Optional value

    /*
        In case of a credit card transaction, the Payment Data Block formation will be as follows:
            paymentDataBlock = 11111111111|4111111111111111|08|2022|123|Soloman|Visa|9820998209|123456|123456|1026|1202
        In case of a Net Banking transaction, the Payment Data Block formation will be as follows:
            paymentDataBlock = 00000000010|1001 // Here we need Gateway ID
        In case of an IMPS transaction, the Payment Data Block formation will be as follows:
            paymentDataBlock = 00000011100|9820998209|123456|123456 // Here we need Customer Mobile Number, Payment ID , OTP
    */

    public $payModeTypeCard     = array('CC','DC','DD');
    public $payModeTypeNetBank  = array('NB');

    // Define the Field Existence Indicator for the Payment Data Block , These are optional values
    public $cardNumber          = ''; // 1. Card Number
    public $expMonth            = ''; // 2. Expiry Month
    public $expYear             = ''; // 3. Expiry Year
    public $CVV                 = ''; // 4. CVV
    public $cardHolderName      = ''; // 5. Card Holder Name
    public $cardType            = ''; // 6. Card Type
    public $custMobileNumber    = ''; // 7. Customer Mobile Number
    public $paymentID           = ''; // 8. Payment ID
    public $OTP                 = ''; // 9. OTP
    public $gatewayID           = ''; // 10.Gateway ID
    public $cardToken           = ''; // 11.Card Token

    // Define the Field Existence Indicator for the Merchant Data Block , These are optional values
    public $UDF1   = '';  // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
    public $UDF2   = '';  // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
    public $UDF3   = '';  // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
    public $UDF4   = '';  // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
    public $UDF5   = '';  // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
    public $UDF6   = '';  // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
    public $UDF7   = '';  // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
    public $UDF8   = '';  // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
    public $UDF9   = '';  // This is a ‘user-defined field’ that can be used to send additional information about the transaction.
    public $UDF10  = '';  // This is a ‘user-defined field’ that can be used to send additional information about the transaction.

    // Define the Field Existence Indicator for the Other Details Data Block , These are optional values
    public $custID                 = '';  // The ID used to identify your customer when their profile was created. This field has to be passed by you only if you have opted for the ‘Tokenization/Stored Card’ feature.
                                          //The field is ‘alphanumeric’; special characters are not allowed.
    public $transactionSource      = '';  // This is used to identify a channel, in case you are using multiple channels.
                                          //Acceptable values are: Web, IVR, or Mobile.
    public $productInfo            = '';  // This field is used to send details about the product and related information.
    public $isUserLoggedIn         = '';  // Indicates whether a customer has signed in to your website, or has done a Guest checkout Values: ‘Y’ – customer has signed in
                                          //‘N’ – customer has done a Guest checkout
    public $itemTotal              = '';  // This is a comma-separated list of the sale value of all the items in the order.
                                          //Example: If the order has two items of value 500 and 1000, send the value as ‘500.00, 1000.00’. Up to 2 decimal places are allowed in the value.
    public $itemCategory           = ''; // This is a comma-separated list of the categories of all the items in the order.
    public $ignoreValidationResult = ''; // Indicates whether you want to proceed for Authorization irrespective of the 3DSecure/Authentication result, or not. TRUE/FALSE

    // Define the Field Existence Indicator for the DCC Data Block, These are optional values
    public $DCCReferenceNumber = ''; // DCC Reference Number
    public $foreignAmount      = ''; // Foreign Amount
    public $ForeignCurrency    = ''; // Foreign Currency


     public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }


    public function customEncryption($arguments){
        // Get the arguments of the class which is an array , assigned with key and property values
        // $arguments = func_get_args();

        if(!empty($arguments)){

            foreach($arguments as $argumentsArray){

                // Set the property for Transaction Array
                if(!empty($arguments['Field_Existence_Indicator_Transaction']))
                    $this->transactionArray = array_filter($arguments['Field_Existence_Indicator_Transaction']);
                // Set the property for Billing Array
                if(!empty($arguments['Field_Existence_Indicator_Billing']))
                    $this->billingArray = array_filter($arguments['Field_Existence_Indicator_Billing']);
                // Set the property for Shipping Array
                if(!empty($arguments['Field_Existence_Indicator_Shipping']))
                    $this->shippingArray = array_filter($arguments['Field_Existence_Indicator_Shipping']);
                // Set the property for Payment Array
                if(!empty($arguments['Field_Existence_Indicator_Payment']))
                    $this->paymentArray = array_filter($arguments['Field_Existence_Indicator_Payment']);
                // Set the property for Merchant Array
                if(!empty($arguments['Field_Existence_Indicator_Merchant']))
                    $this->merchantArray = array_filter($arguments['Field_Existence_Indicator_Merchant']);
                // Set the property for Merchant Array
                if(!empty($arguments['Field_Existence_Indicator_OtherData']))
                    $this->otherDataArray = array_filter($arguments['Field_Existence_Indicator_OtherData']);
                // Set the property for Merchant Array
                if(!empty($arguments['Field_Existence_Indicator_DCC']))
                    $this->DCCArray = array_filter($arguments['Field_Existence_Indicator_DCC']);



                // Case 0
                // Assiging the property values , This is for Network Online
                foreach($arguments['Network_Online_setting'] as $key => $property){
                    if(property_exists($this, $key))
                        $this->{$key} = $property;
                }
                // Case 1 : <Block Existence Indicator> Bitmap values
                // Assiging the property values for the Block Existence Indicator
                foreach($arguments['Block_Existence_Indicator'] as $key => $property){
                    if(property_exists($this, $key))
                        $this->{$key} = $property;
                }
                // Case 2 : <Field Existence Indicator for the Block Transaction> Bitmap values
                // Assiging the property values for the Field Existence Indicator for the Block
                foreach($arguments['Field_Existence_Indicator_Transaction'] as $key => $property){
                    if(property_exists($this, $key))
                        $this->{$key} = $property;
                }
                // Case 3 : <Field Existence Indicator for the Block Billing> Bitmap values
                // Assiging the property values for the Field Existence Indicator for the Block
                foreach($arguments['Field_Existence_Indicator_Billing'] as $key => $property){
                    if(property_exists($this, $key))
                        $this->{$key} = $property;
                }
                // Case 4 : <Field Existence Indicator for the Block Shipping> Bitmap values
                // Assiging the property values for the Field Existence Indicator for the Block
                foreach($arguments['Field_Existence_Indicator_Shipping'] as $key => $property){
                    if(property_exists($this, $key))
                        $this->{$key} = $property;
                }
                // Case 5 : <Field Existence Indicator for the Block Payment> Bitmap values
                // Assiging the property values for the Field Existence Indicator for the Block
                foreach($arguments['Field_Existence_Indicator_Payment'] as $key => $property){
                    if(property_exists($this, $key))
                        $this->{$key} = $property;
                }
                // Case 6 : <Field Existence Indicator for the Block Merchant> Bitmap values
                // Assiging the property values for the Field Existence Indicator for the Block
                foreach($arguments['Field_Existence_Indicator_Merchant'] as $key => $property){
                    if(property_exists($this, $key))
                        $this->{$key} = $property;
                }
                // Case 7 : <Field Existence Indicator for the Block Other Data> Bitmap values
                // Assiging the property values for the Field Existence Indicator for the Block
                foreach($arguments['Field_Existence_Indicator_OtherData'] as $key => $property){
                    if(property_exists($this, $key))
                        $this->{$key} = $property;
                }
                // Case 8 : <Field Existence Indicator for the Block DCC Data> Bitmap values
                // Assiging the property values for the Field Existence Indicator for the Block
                foreach($arguments['Field_Existence_Indicator_DCC'] as $key => $property){
                    if(property_exists($this, $key))
                        $this->{$key} = $property;
                }

                // Check PayMode Type Selected by the Merchant
                if(in_array($this->payModeType, $this->payModeTypeCard)){
                    // For the Credit Card transaction // (CC)-Credit Card, (DC)-Debit Card, (DD)-Direct Debit
                    $this->paymentArray = array_filter($arguments['Field_Existence_Indicator_Payment']);
                }elseif(in_array($this->payModeType, $this->payModeTypeNetBank)){
                    // For the (NB)-Net Banking
                    $this->paymentArray = array('gatewayID' => $this->gatewayID);
                }else{
                    // For IMPS-Hope so (PAYPAL)-PayPal
                    $this->paymentArray = array(
                                                'custMobileNumber' => $this->custMobileNumber,
                                                'paymentID'        => $this->paymentID,
                                                'OTP'              => $this->OTP
                                               );
                }

            }
        }


         // Block Existence Indicator Bitmap property
        $this->blockExistenceIndicator              = $this->setblockExistenceIndicator();

        // Transaction Data Block Properties
        $this->TransactionFieldExistenceIndicator   = $this->setTransactionFieldExistenceIndicator();
        $this->TransactionData                      = join('|',$this->transactionArray);
        $this->TransactionDataBlock1                = $this->TransactionFieldExistenceIndicator.'|'.$this->TransactionData;

        // Billing Data Block Properties
        $this->BillingFieldExistenceIndicator       = $this->setBillingFieldExistenceIndicator();
        if($this->billingDataBlock){
            $this->BillingData                      = join('|',$this->billingArray);
            $this->BillingDataBlock2                = $this->BillingFieldExistenceIndicator.'|'.$this->BillingData;
        }else{
            // Replace all the shippingArray Values to NULL and JOIN the Array with pipe delimiter
            foreach( $this->billingArray as $key => $val ){
                $emptybillingArray[] = str_replace($val,"",$val);
            }
            $this->BillingData                      = join('|',array_filter($emptybillingArray));
            $this->BillingDataBlock2                = $this->BillingFieldExistenceIndicator.$this->BillingData;
        }

        // Shipping Data Block Properties
        $this->ShippingFieldExistenceIndicator      = $this->setShippingFieldExistenceIndicator();
        if($this->shippingDataBlock){
            $this->ShippingData                     = join('|',$this->shippingArray);
            $this->ShippingDataBlock3               = $this->ShippingFieldExistenceIndicator.'|'.$this->ShippingData;
        }else{
            // Replace all the shippingArray Values to NULL and JOIN the Array with pipe delimiter
            foreach( $this->shippingArray as $key => $val ){
                $emptyshippingArray[] = str_replace($val,"",$val);
            }
            $this->ShippingData                     = join('|',array_filter($emptyshippingArray));
            $this->ShippingDataBlock3               = $this->ShippingFieldExistenceIndicator.$this->ShippingData;
        }

        // Payment Data Block Properties
        $this->PaymentFieldExistenceIndicator       = $this->setPaymentFieldExistenceIndicator();
        if($this->paymentDataBlock){
            $this->PaymentData                      = join('|',$this->paymentArray);
            $this->PaymentDataBlock4                = $this->PaymentFieldExistenceIndicator.'|'.$this->PaymentData;
        }else{
            // Replace all the shippingArray Values to NULL and JOIN the Array with pipe delimiter
            foreach( $this->paymentArray as $key => $val ){
                $emptypaymentArray[] = str_replace($val,"",$val);
            }
            $this->PaymentData                      = join('|',array_filter($emptypaymentArray));
            $this->PaymentDataBlock4                = $this->PaymentFieldExistenceIndicator.$this->PaymentData;
        }
        // Merchant Data Block Properties
        $this->MerchantFieldExistenceIndicator      = $this->setMerchantFieldExistenceIndicator();
        if($this->merchantDataBlock){
            $this->MerchantData                     = join('|',$this->merchantArray);
            $this->MerchantDataBlock5               = $this->MerchantFieldExistenceIndicator.'|'.$this->MerchantData;
        }else{
            // Replace all the merchantArray Values to NULL and JOIN the Array with pipe delimiter
            foreach( $this->shippingArray as $key => $val ){
                $emptymerchantArray[] = str_replace($val,"",$val);
            }
            $this->MerchantData                     = join('|',array_filter($emptymerchantArray));
            $this->MerchantDataBlock5               = $this->MerchantFieldExistenceIndicator.$this->MerchantData;
        }

        // Other Data Block Properties
        $this->OtherDetailsFieldExistenceIndicator  = $this->setOtherDetailsFieldExistenceIndicator();
        if($this->otherDataBlock){
            $this->OtherDetailsData                 = join('|',$this->otherDataArray);
            $this->OtherDetailsDataBlock6           = $this->OtherDetailsFieldExistenceIndicator.'|'.$this->OtherDetailsData;
        }else{
            // Replace all the merchantArray Values to NULL and JOIN the Array with pipe delimiter
            foreach( $this->otherDataArray as $key => $val ){
                $emptyotherDataArray[] = str_replace($val,"",$val);
            }
            $this->OtherDetailsData                 = join('|',array_filter($emptyotherDataArray));
            $this->OtherDetailsDataBlock6           = $this->OtherDetailsFieldExistenceIndicator.$this->OtherDetailsData;
        }

        // DCC Data Block Properties
        $this->DCCFieldExistenceIndicator           = $this->setDCCFieldExistenceIndicator();
        if($this->DCCDataBlock){
            $this->DCCData                          = join('|',$this->DCCArray);
            $this->DCCDataBlock7                    = $this->DCCFieldExistenceIndicator.'|'.$this->DCCData;
        }else{
            // Replace all the DCCArray Values to NULL and JOIN the Array with pipe delimiter
            foreach( $this->DCCArray as $key => $val ){
                $emptyDCCDataArray[] = str_replace($val,"",$val);
            }
            $this->DCCData                          = join('|',array_filter($emptyDCCDataArray));
            $this->DCCDataBlock7                    = $this->DCCFieldExistenceIndicator.$this->DCCData;
        }
        // Block_Existence_Indicator Bit map and DataBlock`s Array
        $this->DataBlocksArray                      = array(
                                                            'DataBlockBitmap' => $this->blockExistenceIndicator,
                                                            'DataBlock1'      => $this->TransactionDataBlock1,
                                                            'DataBlock2'      => $this->billingDataBlock ? $this->BillingDataBlock2 : '',
                                                            'DataBlock3'      => $this->shippingDataBlock ? $this->ShippingDataBlock3 : '',
                                                            'DataBlock4'      => $this->paymentDataBlock ? $this->PaymentDataBlock4 : '',
                                                            'DataBlock5'      => $this->merchantDataBlock ? $this->MerchantDataBlock5 : '',
                                                            'DataBlock6'      => $this->otherDataBlock ? $this->OtherDetailsDataBlock6 : '',
                                                            'DataBlock7'      => $this->DCCDataBlock ? $this->DCCDataBlock7 : ''
                                                            );


        $this->beforeEncryptionString               = join('||',array_filter($this->DataBlocksArray));
        $this->EncryptedString                      = $this->encryptData($this->beforeEncryptionString,'tbQUjXpoDvrUbHom5QJ7sqe+d3WaRz5EIxOB6p7HcR4=',$this->iv);
        $this->NeoPostData                          = '201712201000005'.'||'.$this->collaboratorId.'||'.$this->EncryptedString;



        return $this->NeoPostData;

        // // This is puerly for writing logs to check all the values passed to cross check the values passed to NEO


        // if($this->url)
        //     $requestUrl = 'https://NeO.network.ae/direcpay/secure/PaymentTxnServlet';
        // else
        //     $requestUrl = 'https://uat-NeO.network.ae/direcpay/secure/PaymentTxnServlet';

        // $billingDataBlock  = $this->billingDataBlock ? $this->BillingDataBlock2 : 'NULL';
        // $shippingDataBlock = $this->shippingDataBlock ? $this->ShippingDataBlock3 : 'NULL';
        // $paymentDataBlock  = $this->paymentDataBlock ? $this->PaymentDataBlock4 : 'NULL';
        // $merchantDataBlock = $this->merchantDataBlock ? $this->MerchantDataBlock5 : 'NULL';
        // $otherDataBlock    = $this->otherDataBlock ? $this->OtherDetailsDataBlock6 : 'NULL';
        // $DCCDataBlock      = $this->DCCDataBlock ? $this->DCCDataBlock7 : 'NULL';

        // // wirte logs before POSTING values
        // $path = 'neologs.txt';
        // if (file_exists($path)) {     // Make sure we don't create the file
        //     $fp = fopen($path, 'w+');  // Sets the file size to zero bytes
        //     ftruncate($fp, 0);
        //     fclose($fp);
        // }
        // $this->AddLog('Merchant ID : '.$this->merchantId,'1');
        // $this->AddLog('Merchant Key : '.$this->merchantKey,'2');
        // $this->AddLog('Collaborator ID : '.$this->collaboratorId,'3');
        // $this->AddLog('Neo URL  : '.$requestUrl,'4');
        // $this->AddLog('Block Existence Indicator   : '.$this->blockExistenceIndicator,'5');
        // $this->AddLog('DataBlock1 : TransactionDataBlock1   : '.$this->TransactionDataBlock1,'6');
        // $this->AddLog('DataBlock2 : billingDataBlock   : '.$billingDataBlock , '7');
        // $this->AddLog('DataBlock3 : shippingDataBlock   : '.$shippingDataBlock,'8');
        // $this->AddLog('DataBlock4 : paymentDataBlock   : '.$paymentDataBlock,'9');
        // $this->AddLog('DataBlock5 : merchantDataBlock   : '.$merchantDataBlock,'10');
        // $this->AddLog('DataBlock6 : otherDataBlock   : '.$otherDataBlock,'11');
        // $this->AddLog('DataBlock7 : DCCDataBlock   : '.$DCCDataBlock,'12');
        // $this->AddLog('beforeEncryptionString : '.$this->beforeEncryptionString,'13');
        // $this->AddLog('EncryptedString  : '.$this->EncryptedString,'14');
        // $this->AddLog('NeoPostData  : '.$this->NeoPostData,'15');

    }



    public static function AddLog($message, $severity = 2){
            $fp      = fopen('neologs.txt', 'a+');
            $message = strip_tags($message);
            $message = htmlentities((string)$message, ENT_QUOTES, 'utf-8');
            fwrite($fp, "\n".'['.(int)$severity.'] '.$message);
            fclose($fp);
    }

/**
* Encrypts data with required encryption algorithm
* @param string $data string which needs to be encrypted
* @param string $key  key to encrypt data
* @param string $iv   initializes CBC encryption
* @return string      encrypted string
*/
    public function encryptData( $data, $key, $iv ){

            $enc                = $this->encryptMethod;
            $mode               = $this->encryptMode;
            $size               = mcrypt_get_block_size( $enc, $mode );

            $pad                = $size - ( strlen( $data ) % $size );
            $padtext            = $data . str_repeat( chr( $pad ), $pad );
            $crypt              = mcrypt_encrypt( $enc, base64_decode( $key ), $padtext, $mode, $iv );
            $data               = base64_encode( $crypt );
        return $data;
    }
/**
* Decrypts data with required decryption algorithm
* @param string $data string which needs to be decrypted
* @param string $key  key to decrypt data
* @param string $iv   initializes CBC decryption
* @return string      decrypted string
*/
    public function decryptData( $data, $key, $iv ){
        echo "ssaaaa";
        exit();

        if($data){

            list($merchantId,$encryptString) = explode("||", $data);

            $enc          = $this->encryptMethod;
            $mode         = $this->encryptMode;
            $iv           = $iv;
            $encrypt_key  = $key;

            $EncText      = base64_decode($encryptString);
            $padtext      = mcrypt_decrypt($enc, base64_decode($encrypt_key), $EncText, $mode, $iv);
            $pad          = ord($padtext{strlen($padtext) - 1});

            $text         = substr($padtext, 0, -1 * $pad);
            //$reponseParameters = explode("|",$text);
            $reponseArray = explode("||",$text);

            $blockEI             = $reponseArray[0]; // It has to contains Seven indicators
            $bitmapString        = str_split($blockEI);
            $blockEIArrayKey     = array(
                                            'Transaction_Response',                //Same as Request
                                            'Transaction_related_information',    // Transaction related information
                                            'Transaction_Status_information',    //  Transaction Status information
                                            'Merchant_Information',             //   Merchant Information
                                            'Fraud_Block',                     //    Fraud Block
                                            'DCC_Block',                      //     DCC Block
                                            'Additional'                     //      Additional Block Like Card Mask
                                        );
            //
            $bit          = 0;
            $blockEIArray = array();

            foreach($blockEIArrayKey as $blockValues){
                $blockEIArray[$blockValues] = $bitmapString[$bit];
                $bit++;
            }
            $blockEIArray = array_filter($blockEIArray);
            // Remove the first element from Array to map with the bit map values
            array_shift($reponseArray);
            $resposeAssignedArray = array();
            $res                  = 0;
            foreach($blockEIArray as $key => $value){
                    $resposeAssignedArray[$key] =  $reponseArray[$res];
                $res++;
            }
                    $TransactionResposeValue['text']            = $merchantId.'||'.$text;
                    $TransactionResposeValue['merchantId']      = $merchantId;
                    $TransactionResposeValue['DataBlockBitmap'] = $blockEI;
            foreach($blockEIArrayKey as $key => $value){
                    if(isset($resposeAssignedArray[$value]))
                        $TransactionResposeValue[$value] = $resposeAssignedArray[$value];
                    else
                        $TransactionResposeValue[$value] = 'NULL';
            }

            return $TransactionResposeValue;

        }else{
            return false;
        }

    }

    /**
     * setblockExistenceIndicator Function which is used to return the Block Existence Indicator Bitmap
     * Function PARAMETERS : NULL
     * returns : The Block Existence Indicator Bitmap : String
     */
    public function setblockExistenceIndicator(){

            $blockExistenceIndicator  = '';
            // DataBlock 1 transactionDataBlock
            $blockExistenceIndicator .= $this->transactionDataBlock ? '1' : '0';
            // DataBlock 2 billingDataBlock
            $blockExistenceIndicator .= $this->billingDataBlock ? '1' : '0';
            // DataBlock 3 ShippingDataBlock
            $blockExistenceIndicator .= $this->shippingDataBlock ? '1' : '0';
            // DataBlock 4 PaymentDataBlock
            $blockExistenceIndicator .= $this->paymentDataBlock ? '1' : '0';
            // DataBlock 5 MerchantDataBlock
            $blockExistenceIndicator .= $this->merchantDataBlock ? '1' : '0';
            // DataBlock 6 OtherDataBlock
            $blockExistenceIndicator .= $this->otherDataBlock ? '1' : '0';
            // DataBlock 7 DCCDataBlock
            $blockExistenceIndicator .= $this->DCCDataBlock ? '1' : '0';

        return $blockExistenceIndicator;
    }

// Function setTransactionFieldExistenceIndicator Filed Existence Indicator (BEI)
// for Transaction Block Data
/*
 * setTransactionFieldExistenceIndicator
 * Get all the required property values form the constructor to generate the Bitmap value and the POSTING data
   for the Transaction Block Data, Check if the transactionDataBlock is set to true then generate the Bitmap values
 * @returns Bitmap value for the Transaction Block Data
 *
 */
    public function setTransactionFieldExistenceIndicator(){

        // Check if the transactionDataBlock is set true
        if($this->transactionDataBlock){

                $transactionFieldEI = '';
                // Filed 1 Merchant Order Number For Transaction Data Block 1
                $transactionFieldEI .= $this->merchantOrderNumber ? '1':'0';
                // Filed 2 Amount For Transaction Data Block 1
                $transactionFieldEI .= $this->amount ? '1':'0';
                // Filed 3 SuccessUrl For Transaction Data Block 1
                $transactionFieldEI .= $this->successUrl ? '1':'0';
                // Filed 4 FailureUrl For Transaction Data Block 1
                $transactionFieldEI .= $this->failureUrl ? '1':'0';
                // Filed 5 TransactionMode For Transaction Data Block 1
                $transactionFieldEI .= $this->transactionMode ? '1':'0';
                // Filed 6 PayModeType For Transaction Data Block 1
                $transactionFieldEI .= $this->payModeType ? '1':'0';
                // Filed 7 TransactionType For Transaction Data Block 1
                $transactionFieldEI .= $this->transactionType ? '1':'0';
                // Filed 8 Currency For Transaction Data Block 1
                $transactionFieldEI .= $this->currency ? '1':'0';

            return $transactionFieldEI;
        }else{
            return '00000000';
        }

    }
// Function setBillingFieldExistenceIndicator Filed Existence Indicator (BEI)
// for Billing Block Data
/*
 * setBillingFieldExistenceIndicator
 * Get all the required property values form the constructor to generate the Bitmap value
   for the Billing Block Data, Check if the billingDataBlock is set to true then generate the Bitmap values
 * @returns Bitmap value for the Billing Block Data
 *
 */
    public function setBillingFieldExistenceIndicator(){

        if($this->billingDataBlock){

                $billingFieldEI  = '';
                // Filed 1 BillTo FirstName For Billing Data Block 2
                $billingFieldEI .= $this->billToFirstName ? '1':'0';
                // Filed 2 BillTo billToLastName For Billing Data Block 2
                $billingFieldEI .= $this->billToLastName ? '1':'0';
                // Filed 3 BillTo billToStreet1 For Billing Data Block 2
                $billingFieldEI .= $this->billToStreet1 ? '1':'0';
                // Filed 4 BillTo billToStreet2 For Billing Data Block 2
                $billingFieldEI .= $this->billToStreet2 ? '1':'0';
                // Filed 5 BillTo billToCity For Billing Data Block 2
                $billingFieldEI .= $this->billToCity ? '1':'0';
                // Filed 6 BillTo billToState For Billing Data Block 2
                $billingFieldEI .= $this->billToState ? '1':'0';
                // Filed 7 BillTo billtoPostalCode For Billing Data Block 2
                $billingFieldEI .= $this->billtoPostalCode ? '1':'0';
                // Filed 8 BillTo billToCountry For Billing Data Block 2
                $billingFieldEI .= $this->billToCountry ? '1':'0';
                // Filed 9 BillTo billToEmail For Billing Data Block 2
                $billingFieldEI .= $this->billToEmail ? '1':'0';
                // Filed 10 BillTo billToMobileNumber For Billing Data Block 2
                $billingFieldEI .= $this->billToMobileNumber ? '1':'0';
                // Filed 11 BillTo billToPhoneNumber1 For Billing Data Block 2
                $billingFieldEI .= $this->billToPhoneNumber1 ? '1':'0';
                // Filed 12 BillTo billToPhoneNumber2 For Billing Data Block 2
                $billingFieldEI .= $this->billToPhoneNumber2 ? '1':'0';
                // Filed 13 BillTo billToPhoneNumber3 For Billing Data Block 2
                $billingFieldEI .= $this->billToPhoneNumber3 ? '1':'0';

            return $billingFieldEI;
        }else{
            return '0000000000000';
        }
    }
// Function setShippingFieldExistenceIndicator Filed Existence Indicator (BEI)
// for Shipping Block Data
/*
 * setShippingFieldExistenceIndicator
 * Get all the required property values form the constructor to generate the Bitmap value
   for the Shipping Block Data, Check if the shippingDataBlock is set to true then generate the Bitmap values
 * @returns Bitmap value for the Shipping Block Data
 *
 */
    public function setShippingFieldExistenceIndicator(){

        if($this->shippingDataBlock){

                $shippingFieldEI  = '';
                // Filed 1 shipToFirstName For Shipping Data Block 3
                $shippingFieldEI .= $this->shipToFirstName ? '1':'0';
                // Filed 2 shipToLastName For Shipping Data Block 3
                $shippingFieldEI .= $this->shipToLastName ? '1':'0';
                // Filed 3 shipToStreet1 For Shipping Data Block 3
                $shippingFieldEI .= $this->shipToStreet1 ? '1':'0';
                // Filed 4 shipToStreet2 For Shipping Data Block 3
                $shippingFieldEI .= $this->shipToStreet2 ? '1':'0';
                // Filed 5 shipToCity For Shipping Data Block 3
                $shippingFieldEI .= $this->shipToCity ? '1':'0';
                // Filed 6 shipToState For Shipping Data Block 3
                $shippingFieldEI .= $this->shipToState ? '1':'0';
                // Filed 7 shipToPostalCode For Shipping Data Block 3
                $shippingFieldEI .= $this->shipToPostalCode ? '1':'0';
                // Filed 8 shipToCountry For Shipping Data Block 3
                $shippingFieldEI .= $this->shipToCountry ? '1':'0';
                // Filed 9 shipToPhoneNumber1 For Shipping Data Block 3
                $shippingFieldEI .= $this->shipToPhoneNumber1 ? '1':'0';
                // Filed 10 shipToPhoneNumber2 For Shipping Data Block 3
                $shippingFieldEI .= $this->shipToPhoneNumber2 ? '1':'0';
                // Filed 11 shipToPhoneNumber3 For Shipping Data Block 3
                $shippingFieldEI .= $this->shipToPhoneNumber3 ? '1':'0';
                // Filed 12 shipToMobileNumber For Shipping Data Block 3
                $shippingFieldEI .= $this->shipToMobileNumber ? '1':'0';

            return $shippingFieldEI;

        }else{
           return '000000000000';
        }

    }
// Function setPaymentFieldExistenceIndicator Filed Existence Indicator (BEI)
// for Payment Block Data
/*
 * setPaymentFieldExistenceIndicator
 * Get all the required property values form the constructor to generate the Bitmap value
   for the Payment Block Data, Check if the paymentDataBlock is set to true then generate the Bitmap values
 * @returns Bitmap value for the Payment Block Data
 *
 */
    public function setPaymentFieldExistenceIndicator(){

        if($this->paymentDataBlock){

            // Here we are having three options
                // 1 ) Credit Card transaction // (CC)-Credit Card, (DC)-Debit Card, (DD)-Direct Debit // BitMap : 11111111111
                // 2 ) (NB)-Net Banking // BitMap : 00000000010
                // 3 ) IMPS-Hope so (PAYPAL)-PayPal //  BitMap : 00000011100

            $creditArray = array('CC','DC','DD');
            if(in_array($this->payModeType, $creditArray)){
                        $paymentFieldEI = '';
                        // Filed 1 cardNumber For Payment Data Block 4
                        $paymentFieldEI .= $this->cardNumber ? '1':'0';
                        // Filed 2 expMonth For Payment Data Block 4
                        $paymentFieldEI .= $this->expMonth ? '1':'0';
                        // Filed 3 expYear For Payment Data Block 4
                        $paymentFieldEI .= $this->expYear ? '1':'0';
                        // Filed 4 CVV For Payment Data Block 4
                        $paymentFieldEI .= $this->CVV ? '1':'0';
                        // Filed 5 cardHolderName For Payment Data Block 4
                        $paymentFieldEI .= $this->cardHolderName ? '1':'0';
                        // Filed 6 cardType For Payment Data Block 4
                        $paymentFieldEI .= $this->cardType ? '1':'0';
                        // Filed 7 custMobileNumber For Payment Data Block 4
                        $paymentFieldEI .= $this->custMobileNumber ? '1':'0';
                        // Filed 8 paymentID For Payment Data Block 4
                        $paymentFieldEI .= $this->paymentID ? '1':'0';
                        // Filed 9 OTP For Payment Data Block 4
                        $paymentFieldEI .= $this->OTP ? '1':'0';
                        // Filed 10 gatewayID For Payment Data Block 4
                        $paymentFieldEI .= $this->gatewayID ? '1':'0';
                        // Filed 11 cardToken For Payment Data Block 4
                        $paymentFieldEI .= $this->cardToken ? '1':'0';

                    return $paymentFieldEI;

            }elseif($this->payModeType=='NB'){
                        $paymentFieldEI = '00000000010';
                    return $paymentFieldEI;
            }else{
                        $paymentFieldEI = '00000011100';
                    return $paymentFieldEI;
            }
        }else{
                        $paymentFieldEI = '00000000000';
                    return $paymentFieldEI;
        }
    }

// Function setMerchantFieldExistenceIndicator Filed Existence Indicator (BEI)
// for Merchant Block Data
/*
 * setMerchantFieldExistenceIndicator
 * Get all the required property values form the constructor to generate the Bitmap value
   for the Merchant Block Data, Check if the merchantDataBlock is set to true then generate the Bitmap values
 * @returns Bitmap value for the Merchant Block Data
 *
 */
    public function setMerchantFieldExistenceIndicator(){

        if($this->merchantDataBlock){
            $merchantFieldEI  = '';
            // Filed 1 UDF1 For Merchant Data Block 5
            $merchantFieldEI .= $this->UDF1 ? '1':'0';
            // Filed 2 UDF2 For Merchant Data Block 5
            $merchantFieldEI .= $this->UDF2 ? '1':'0';
            // Filed 3 UDF3 For Merchant Data Block 5
            $merchantFieldEI .= $this->UDF3 ? '1':'0';
            // Filed 4 UDF4 For Merchant Data Block 5
            $merchantFieldEI .= $this->UDF4 ? '1':'0';
            // Filed 5 UDF5 For Merchant Data Block 5
            $merchantFieldEI .= $this->UDF5 ? '1':'0';
            // Filed 6 UDF6 For Merchant Data Block 5
            $merchantFieldEI .= $this->UDF6 ? '1':'0';
            // Filed 7 UDF7 For Merchant Data Block 5
            $merchantFieldEI .= $this->UDF7 ? '1':'0';
            // Filed 8 UDF8 For Merchant Data Block 5
            $merchantFieldEI .= $this->UDF8 ? '1':'0';
            // Filed 9 UDF9 For Merchant Data Block 5
            $merchantFieldEI .= $this->UDF9 ? '1':'0';
            // Filed 10 UDF10 For Merchant Data Block 5
            $merchantFieldEI .= $this->UDF10 ? '1':'0';

            return $merchantFieldEI;

        }else{
            return '0000000000';
        }

    }

// Function setOtherDetailsFieldExistenceIndicator Filed Existence Indicator (BEI)
// for Other Details Block Data
/*
 * setOtherDetailsFieldExistenceIndicator
 * Get all the required property values form the constructor to generate the Bitmap value
   for the Other Details Block Data, Check if the otherDataBlock is set to true then generate the Bitmap values
 * @returns Bitmap value for the Other Details Block Data
 *
 */
    public function setOtherDetailsFieldExistenceIndicator(){

        if($this->otherDataBlock){

                $otherFieldEI  = '';
                // Filed 1 custID For OtherDeatils Data Block 6
                $otherFieldEI .= $this->custID ? '1':'0';
                // Filed 2 transactionSource For OtherDeatils Data Block 6
                $otherFieldEI .= $this->transactionSource ? '1':'0';
                // Filed 3 productInfo For OtherDeatils Data Block 6
                $otherFieldEI .= $this->productInfo ? '1':'0';
                // Filed 4 isUserLoggedIn For OtherDeatils Data Block 6
                $otherFieldEI .= $this->isUserLoggedIn ? '1':'0';
                // Filed 5 itemTotal For OtherDeatils Data Block 6
                $otherFieldEI .= $this->itemTotal ? '1':'0';
                // Filed 6 itemCategory For OtherDeatils Data Block 6
                $otherFieldEI .= $this->itemCategory ? '1':'0';
                // Filed 7 ignoreValidationResult For OtherDeatils Data Block 6
                $otherFieldEI .= $this->ignoreValidationResult ? '1':'0';

            return $otherFieldEI;

        }else{
            return '0000000';
        }
    }

// Function setDCCFieldExistenceIndicator Filed Existence Indicator (BEI)
// for DCC Block Data
/*
 * setDCCFieldExistenceIndicator
 * Get all the required property values form the constructor to generate the Bitmap value
   for the DCC Block Data, Check if the DCCDataBlock is set to true then generate the Bitmap values
 * @returns Bitmap value for the DCC Block Data
 *
 */
    public function setDCCFieldExistenceIndicator(){

        if($this->DCCDataBlock){
                $DCCFieldEI  = '';
                // Filed 1 DCCReferenceNumber For DCC Data Block 7
                $DCCFieldEI .= $this->DCCReferenceNumber ? '1':'0';
                // Filed 1 foreignAmount For DCC Data Block 7
                $DCCFieldEI .= $this->foreignAmount ? '1':'0';
                // Filed 1 ForeignCurrency For DCC Data Block 7
                $DCCFieldEI .= $this->ForeignCurrency ? '1':'0';

            return $DCCFieldEI;

        }else{
            return '000';
        }

    }

}
