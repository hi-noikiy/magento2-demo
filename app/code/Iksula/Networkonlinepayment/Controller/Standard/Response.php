<?php

namespace Iksula\Networkonlinepayment\Controller\Standard;

class Response extends \Iksula\Networkonlinepayment\Controller\NetworkAbstract {


    public $encryptMethod =  MCRYPT_RIJNDAEL_128;
    public $encryptMode   =  MCRYPT_MODE_CBC;


    public function execute() {

        $returnUrl = $this->getCheckoutHelper()->getUrl('checkout');

        try {
            $paymentMethod = $this->getPaymentMethod();

            $requestParam = $this->getRequest()->getParams();

            $key = "tbQUjXpoDvrUbHom5QJ7sqe+d3WaRz5EIxOB6p7HcR4=";
            $resparam = $requestParam['responseParameter'];
            $vi = '0123456789abcdef';
            $paramsqq = $this->decryptData($resparam,$key,$vi);
            
            /*Start Logger added*/
                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/network_payment.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $logger->info(print_r($paramsqq, true));
            /*End Logger added*/

            $transaction_status = $paramsqq['Transaction_Status_information'];
            $transaction_statusArray = explode('|', $transaction_status);
            $status = $transaction_statusArray[1];

            $transaction_res_status = $paramsqq['Transaction_Response'];
            $transaction_res_statusArray = explode('|', $transaction_res_status);
            $order_id = $transaction_res_statusArray[1];


            if ($status == 'SUCCESS') {

                $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/success');
                $order = $this->getOrderById($order_id);
                $order->setStatus('processing')->setState('processing');
                $order->save();
            } else {
                // $this->messageManager->addErrorMessage(__('Payment failed. Please try again or choose a different payment method'));
                $order = $this->getOrderById($order_id);
                $order->setStatus('pending_payment')->setState('pending_payment');
                $order->save();
                $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/failure');
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t place the order.'));
        }

        $this->getResponse()->setRedirect($returnUrl);
    }



    public function decryptData( $data, $key, $iv ){
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

}
