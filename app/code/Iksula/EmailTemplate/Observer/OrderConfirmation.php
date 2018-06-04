<?php
namespace Iksula\EmailTemplate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class OrderConfirmation implements ObserverInterface
{
    protected $_logger;
    protected $orderModel; 
    protected $orderSender;
    protected $checkoutSession;
    protected $salescustomattribute;
    protected $state;
    protected $productRepository;
    
    public function __construct(
        \Iksula\EmailTemplate\Helper\Email $email,
        LoggerInterface $logger,
        \Magento\Sales\Model\OrderFactory $orderModel,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Iksula\Report\Model\SalesCustomAttribute $salescustomattribute,
        \Magento\Framework\App\State $state,
        \Magento\Catalog\Model\ProductRepository $productRepository
    )
    {
        $this->email = $email;
        $this->_logger = $logger;
        $this->orderModel = $orderModel;
        $this->orderSender = $orderSender;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->salescustomattribute = $salescustomattribute;
        $this->state = $state;
        $this->productRepository = $productRepository;
    }
 
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        try {        
            if(!$this->state->getAreaCode()){
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
            } 
            $orderIds = $observer->getEvent()->getOrderIds();
            if($orderIds){
                $orderID = $orderIds[0];
                if($orderID){
                    $ordersplitlogicHelper = $objectManager->get('\Iksula\Ordersplit\Helper\Ordersplitlogic');
                    $ordersplitlogicHelper->OrdersplitOfOrders($orderID);
                    // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $order_data = $objectManager->create('\Magento\Sales\Model\Order')->load($orderID);
                    // $order_data = $this->orderModel->create()->load($orderID);
                    $increment_id = $order_data->getIncrementId();
                    $shippingAddressObj = $order_data->getShippingAddress();
                    $shippingAddressArray = $shippingAddressObj->getData();
                    if($shippingAddressArray){
                        try {                
                            $number_ne = $shippingAddressArray['telephone'];
                            $number = (int)str_replace("-","",$number_ne);
                            $sub_payment_mode = '';  
                            $payment_method_code = $order_data->getPayment()->getMethodInstance()->getCode();        
                            if($payment_method_code == "network" && $order_data['credit_amount'] > 0)
                            {            
                                $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
                                $sub_payment_mode =  $scopeConfig->getValue('payment/free/title');
                            }

                            $event = $observer->getEvent()->getName(); 
                            $this->_logger->info("\n" . '[OBSERVER EVENT CATCHED]: ' . $event . "\n");
                            

                            /********************12-01-2018**********************/
                            $shipping_address = $shippingAddressArray['street'].", ".$shippingAddressArray['city'].", ".$shippingAddressArray['region'];
                            $payment = $order_data->getPayment();
                            $method = $payment->getMethodInstance();
                            $country_id = $shippingAddressArray['country_id'];
                            $payment_title = $method->getTitle();
                            $date_time = $objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime');
                            $country = $objectManager->create('Magento\Directory\Model\Country')->loadByCode($country_id);
                            $country_name = $country->getName();
                            /********************12-01-2018**********************/

                            foreach ($order_data->getAllItems() as $item) {
                                    $order_data_info = $item->getData();
                                    /********************12-01-2018**********************/
                                    $ddate = date('Y-m-d',strtotime($order_data_info['created_at']));
                                    $week = $date_time->gmtDate("W",$ddate);
                                    /********************12-01-2018**********************/
                                    $qty = $order_data_info['qty_ordered'];
                                    $item_id = $order_data_info['item_id'];
                                    $product_id = $order_data_info['product_id'];
                                    $order_id = $order_data_info['order_id'];
                                    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
                                    //$product = $this->productRepository->getById($product_id);                
                                    $brand = $product->getAttributeText('brand');
                                    $dimensions = $product->getDimensions();
                                    $weight = $product->getWeight();
                                    $height = $product->getHeight();
                                    $width = $product->getWidth(); 
                                    $depth = $product->getDepth();
                                    $color = $product->getAttributeText('product_color');
                                    $price = $product->getPrice();
                                    $original_price = $product->getSpecialPrice();
                                    if($original_price > 0)
                                    {
                                        $discount_amount = $price - $original_price;
                                        $discount_percent = round(($original_price - $price) * 100 / $original_price);
                                    }
                                    else
                                    {
                                        $discount_percent = 0;
                                        $discount_amount = 0;
                                    } 


                                    $model = $objectManager->create('Iksula\Report\Model\SalesCustomAttribute');
                                    $model->setData('sales_item_id', $item_id);
                                    $model->setData('product_id', $product_id);
                                    $model->setData('order_id', $order_id);
                                    $model->setData('brand', $brand);
                                    $model->setData('dimensions', $dimensions);
                                    $model->setData('weight', $weight);
                                    $model->setData('height', $height);
                                    $model->setData('width', $width);
                                    $model->setData('depth', $depth);
                                    $model->setData('color', $color);
                                    $model->setData('discount_amount', $discount_amount);
                                    $model->setData('discount_percent', $discount_percent);
                                    /********************12-01-2018**********************/
                                    $model->setData('order_week', $week);
                                    $model->setData('payment_title', $payment_title);
                                    $model->setData('shipping_address', $shipping_address);
                                    $model->setData('country_name', $country_name);
                                    $model->setData('sub_payment_mode', $sub_payment_mode);
                                    $model->setData('price', $price);
                                    $model->setData('special_price', $original_price);
                                    $model->setData('increment_id', $increment_id);
                                    $model->setData('qty', $qty);
                                    /********************12-01-2018**********************/
                                    $model->save();

                                     //echo "<pre>";
                                    //print_r(order_data_info);
                                }

                            if(count($orderIds))
                            { 
                               $is_enable =  $this->scopeConfig->getValue('sales_email/order/enable');
                                $template_path =  $this->scopeConfig->getValue('sales_email/order/order_sms');
                                $data = array(
                                'order_id' => $increment_id
                                );
                                
                                if($is_enable)
                                    $this->email->smsTemplate($template_path, $data, $number);

                                $result['email'] =  $this->scopeConfig->getValue('sms_configuration/sms_setting/domain_email_id');
                                $result['name'] = $this->scopeConfig->getValue('sms_configuration/sms_setting/domain_name');

                                $receiver['email'] = $this->scopeConfig->getValue('sms_configuration/sms_setting/domain_email_id');
                                $receiver['name'] = $this->scopeConfig->getValue('sms_configuration/sms_setting/domain_name');
                                
                                $this->email->emailTemplate('order_confirmation_admn',$data ,$result , $receiver, '','');

                            }
                        } catch (Exception $e) {
                            $this->_logger->critical('Order confirmation observer log error', ['exception' => $e->getMessage()]);
                            $this->_logger->info('Order confirmation observer log error : '.$e->getMessage());  
                        }

                    }else{
                        // $this->_logger->log(\Psr\Log\LogLevel::DEBUG,'Order confirmation observer log error', 'Shipping address not found');
                        $this->_logger->info('Order confirmation observer log error : Shipping address not found '); 

                    }
                }else{
                    // $this->_logger->log(\Psr\Log\LogLevel::DEBUG,'Order confirmation observer log error', 'Order Id not found');
                    $this->_logger->info('Order confirmation observer log error : Order Id not found ');
                }
            }else{
                 // $this->_logger->log(\Psr\Log\LogLevel::DEBUG,'Order confirmation observer log error', 'Order Ids not found');
                 $this->_logger->info('Order confirmation observer log error : Order Ids not found ');
            }
        } catch (Exception $e) {
            $this->_logger->critical('Order confirmation observer log error', ['exception' => $e->getMessage()]);
            $this->_logger->info('Order confirmation observer log error : '.$e->getMessage()); 

        }
    }
}