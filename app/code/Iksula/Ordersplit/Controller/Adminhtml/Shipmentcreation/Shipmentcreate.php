<?php
namespace Iksula\Ordersplit\Controller\Adminhtml\Shipmentcreation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class  Shipmentcreate extends Action
{

    protected $_resultPageFactory;
    protected $ordersplitFactory;
    protected $_orderRepository;
    protected $_invoiceService;
    protected $transaction;
    protected $salesOrderItemsFactory;
    protected $orderFactoryData;
    protected $scopeConfig;
    protected $emailidshelper;
    protected $fetchrhelper;
    protected $scopeConfigObject;


    public function __construct(Context $context,PageFactory $resultPageFactory,
                                \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory,
                                \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
                                \Magento\Sales\Model\Service\InvoiceService $invoiceService,
                                \Magento\Framework\DB\Transaction $transaction,
                                \Magento\Sales\Model\Order\ItemFactory $salesOrderItemsFactory,
                                \Magento\Sales\Model\OrderFactory    $orderFactoryData,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
                                ,\Iksula\EmailTemplate\Helper\Email $emailidshelper
                                ,\Iksula\Fetchrapi\Helper\Data $fetchrhelper
                                ,\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigObject
                              ) {

        $this->_resultPageFactory = $resultPageFactory;
        $this->ordersplitFactory = $ordersplitFactory;
        $this->_orderRepository = $orderRepository;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->salesOrderItemsFactory = $salesOrderItemsFactory;
        $this->orderFactoryData = $orderFactoryData;
        $this->scopeConfig = $scopeConfig;
        $this->emailidshelper = $emailidshelper;
        $this->fetchrhelper = $fetchrhelper;
        $this->scopeConfigObject = $scopeConfigObject;
        parent::__construct($context);
    }


    function getCustomdetailsforlogistic($order_id , $bags_counts , $order_row_itemid){

      $order = $this->_orderRepository->get($order_id);
      $aOrderdata = $order->getData();
      $ordersplitObject = $this->ordersplitFactory->create()->load($order_row_itemid)->getData();

       $aOrderItemsData = json_decode($ordersplitObject['order_items_data'] , true);

       foreach($aOrderItemsData as $key => $ItemsId){
              $aOrderItemsIds [] = $ItemsId['order_items_id'];
       }

       $aOrderItemsIds = array_unique($aOrderItemsIds);

       foreach($aOrderItemsIds as $OrderitemsValue){
         $aOrderItemsCollection = $this->salesOrderItemsFactory->create()->load($OrderitemsValue)->getData();
         $aProductName []= $aOrderItemsCollection['name'];
       }

       $sProductName = implode(',' , $aProductName);

      $iGrandTotal = (int)$aOrderdata['grand_total'];

      $bags_counts = (int)$bags_counts;
      $aShippingaddressObj = $order->getShippingAddress();
      $aOrderaddressData = $aShippingaddressObj->getData();
      $address_data = $aOrderaddressData['region'].','. $aOrderaddressData['city'] . ',' . $aOrderaddressData['street'];
      $username = trim($this->scopeConfigObject->getValue('fetchr_config_main/fetchr_config/fetchr_username'));
      $password = trim($this->scopeConfigObject->getValue('fetchr_config_main/fetchr_config/fetchr_password'));
      $client_id = trim($this->scopeConfigObject->getValue('fetchr_config_main/fetchr_config/fetchr_client_address_id'));

      $logisticdataFormat =
      array("username" => $username , "password" => $password , "client_address_id" => $client_id ,
          "data" => array(
            array(
              "order_reference" => $ordersplitObject['order_item_id'],
              "name"=> $aOrderaddressData['firstname'] . ' ' . $aOrderaddressData['lastname'],
              "email"=> $aOrderaddressData['email'],
              "phone_number"=> str_replace('-' , '' , $aOrderaddressData['telephone']),
              "alternate_phone"=> str_replace('-' , '' , $aOrderaddressData['telephone']),
              "address"=> $address_data,
              "receiver_country"=> "United Arab Emirates",
              "receiver_city"=> $aOrderaddressData['region'],
              "payment_type"=> "CD",
              "bag_count"=> $bags_counts,
              "weight"=> "",
              "description"=> $sProductName,
              "comments"=> "",
              "order_package_type"=> "",
              "total_amount"=> $iGrandTotal,
              "latitude"=> "",
              "longitude"=> "",
              "extra_data" => array(
              "custom_field"=> "",
              "origin_client_name"=> "")
            )
          )
        );

    return $logisticdataFormat;

    }

    public function execute()
    {

      //$this->fetchrhelper->createTrackingNofetchrapi($data);

      $order_row_itemid = $this->getRequest()->getParam('row_id');
      $bags_counts = $this->getRequest()->getParam('bags_counts');

      $ordersplitobj = $this->ordersplitFactory->create()->load($order_row_itemid);
      $order_items_data = $ordersplitobj->getOrderItemsData();
      $orderId = $ordersplitobj->getOrderId();

        $logisticdata = $this->getCustomdetailsforlogistic($orderId , $bags_counts , $order_row_itemid);
        $result = $this->fetchrhelper->createTrackingNofetchrapi($logisticdata , $ordersplitobj->getOrderItemId());
        $aResult = json_decode($result , true);
        if(strtolower($aResult['status']) == 'success'){
              $tracking_number = $aResult['data'][0]['tracking_no'];
              $awb_link = $aResult['data'][0]['awb_link'];
            $aOrderItemsData = json_decode($order_items_data , true);
            $itemsArray = array();
            /*****************************************/

             $order = $this->_orderRepository->get($orderId);
                  // Check if order can be shipped or has already shipped
                  /*if (! $order->canShip()) {
                  throw new \Magento\Framework\Exception\LocalizedException(
                                __('You can\'t create an shipment.')
                            );
                  }*/

                  // Initialize the order shipment object
                  $convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
                  $shipment = $convertOrder->toShipment($order);
                  $order_incrementid = $order->getIncrementId();

                  foreach($aOrderItemsData as $key => $OrderitemsValues){

                    //$qtyShipped = $orderItem->getQtyToShip();
                    $orderItem = $this->salesOrderItemsFactory->create()->load($OrderitemsValues['order_items_id']);

                    // Create shipment item with qty
                    $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($OrderitemsValues['inventory']);

                    // Add shipment item to shipment
                    $shipment->addItem($shipmentItem);
                  }

                  $shippingCarrierCode = 'custom';
                  $shippingTitle       = 'Fetchr Logistic';
                  $trackingNumber      = $tracking_number;



                  $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\Track')
                  ->setNumber($trackingNumber)
                  ->setCarrierCode($shippingCarrierCode)
                  ->setTitle($shippingTitle);
                  // Register shipment
                  $shipment->register();

                  $shipment->getOrder()->setIsInProcess(true);

                  try {
                  // Save created shipment and order
                  $shipment->addTrack($track)
                    ->save();
                  $shipment->save();
                  $shipment->getOrder()->save();

                  // Send email
                  $this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                    ->notify($shipment);

                  $shipment->save();
                  $ordersplitobj->setShipmentId($shipment->getIncrementId());
                  $ordersplitobj->setShipmentStatus(1);
                  $ordersplitobj->setAwbLink($awb_link);
                  $ordersplitobj->setOrderItemStatus('store_shipped');
                  $ordersplitobj->save();


                  $emailTempVariables = array('order_id' => $orderId , 'shipped_id' => $shipment->getIncrementId(), 'increment_id' => $order->getIncrementId());
                  $receiver['email'] = $this->scopeConfig->getValue('sms_configuration/sms_setting/domain_email_id');
                  $receiver['name'] = $this->scopeConfig->getValue('sms_configuration/sms_setting/domain_name');

                  $senderInfo = ['name' => $receiver['name'] , 'email' => $receiver['email']];
                  $receiverInfo = ['name' => '2xl' , 'email' => $receiver['email']];

                  $this->emailidshelper->emailTemplate('order_shipped_from_store' , $emailTempVariables ,$senderInfo,$receiverInfo,'' , '');

                  $is_enable =  $this->scopeConfig->getValue('sms_configuration/sms_setting/enable_shipped_from_store');
                                  $template_path =  $this->scopeConfig->getValue('sms_configuration/sms_setting/order_shipped_from_store');

                                  $data = array(
                                  'order_id' => $order_incrementid,
                                  'shipment_id' => $shipment->getIncrementId()
                                  );

                                  $AdminNumber  = $this->scopeConfig->getValue('sms_configuration/sms_setting/admin_number');

                                  $aNumber = array($AdminNumber);

                                  if($is_enable)
                                      $this->emailidshelper->smsTemplate($template_path, $data, $aNumber);



                  } catch (\Exception $e) {
                  throw new \Magento\Framework\Exception\LocalizedException(
                                __($e->getMessage())
                            );
                            echo 'Shipment Not done ';
                  }
                  echo 'Shipment Done';
        }else{
          echo 'Shipment Not done because of some error occurred in Fetchr API';
        }


    }


}
