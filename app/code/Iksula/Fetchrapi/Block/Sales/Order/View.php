<?php

namespace Iksula\Fetchrapi\Block\Sales\Order;

class View extends \Magento\Sales\Block\Order\View
{


  public function __construct(
      \Magento\Framework\View\Element\Template\Context $context,
      \Magento\Framework\Registry $registry,
      \Magento\Framework\App\Http\Context $httpContext,
      \Magento\Payment\Helper\Data $paymentHelper,
      array $data = []
  ) {
    $this->_paymentHelper = $paymentHelper;
    $this->_coreRegistry = $registry;
    $this->httpContext = $httpContext;
    parent::__construct($context, $registry , $httpContext , $paymentHelper , $data);
    $this->_isScopePrivate = true;
  }

  public function getOrderid(){
    $order = parent::getOrder();
      $order_id = $order->getId();
      return $order_id;

  }


  public function getTrackingNumberbyShipment(){

    $aShipmentItemsCollectionData = array();

    $order_id = $this->getOrderid();

      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $ordersplitCollection = $objectManager->create('Iksula\Ordersplit\Model\OrdersplitsFactory')->create()
            ->getCollection()
            ->addFieldToFilter('order_id' , array('eq' => $order_id))
            ->getData();

            $aShipment_id = array();

            foreach($ordersplitCollection as $ordersplitDetails){
                if(!$ordersplitDetails['shipment_id'] == "" && isset($ordersplitDetails['shipment_id']))
                  $aShipment_id [] = $ordersplitDetails['shipment_id'];
            }

            $aShipment_id = array_unique($aShipment_id);
            $aTrackingNumber = array();


            if(!empty($aShipment_id)){

              /****************** Shipment Items Details to get Product Details ********************/
            $j=0;
            $aShipmentItemsCollectionData = array();
            foreach($aShipment_id as $shipmentid){

              $sShipmentid = $objectManager->create('Magento\Sales\Model\Order\Shipment')
                    ->getCollection()
                    ->addFieldToFilter('increment_id' , array('eq' => $shipmentid))->getData();

                    $sShipmentid = $sShipmentid[0]['entity_id'];


                    $aShipmentItemsCollection = $objectManager->create('Magento\Sales\Model\Order\Shipment\Item')
                          ->getCollection()
                          ->addFieldToFilter('parent_id' , array('eq' => $sShipmentid))->getData();

                          //$j=0;
                          //$aShipmentCollection = array();
                          foreach($aShipmentItemsCollection as $aShipmentItemsdata){
                              $sProductCollection = $objectManager->create('Magento\Catalog\Model\ProductFactory');
                            $productObj = $sProductCollection->create()->load($aShipmentItemsdata['product_id']);

                            $productimage  = $productObj->getData('thumbnail');
                            $producturl = $productObj->getProductUrl();

                            $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                            $baseurl = $storeManager->getStore()->getBaseUrl();

                            $product_fullimage = $baseurl.'pub/media/catalog/product/'.$productimage;
                            $handle = get_headers($product_fullimage ,1 );

                            if(strpos($handle [0] , '200' ) !== false){

                            }else{
                              $OrdersplitHelper = $objectManager->create('Iksula\Ordersplit\Helper\Data');
                              $product_fullimage = $OrdersplitHelper->getDefaultImageforProduct();
                            }
                            $aShipmentItemsCollectionData[$j]['product_name'] = $aShipmentItemsdata['name'];
                            $aShipmentItemsCollectionData[$j]['product_image'] = $product_fullimage;
                            $aShipmentItemsCollectionData[$j]['product_url'] = $producturl;

                            $j++;

                          }
                    $shipmenttracknumber = $objectManager->create('Magento\Sales\Model\Order\Shipment\Track')
                          ->getCollection()
                          ->addFieldToFilter('parent_id' , array('eq' => $sShipmentid))->getData();

                          $shipmenttracknumber = $shipmenttracknumber[0]['track_number'];


                          $aTrackingNumber [] = $shipmenttracknumber;
            }
          }
          /**********************************************************/
            $aTrackingNumber = array_unique($aTrackingNumber);

            return array($aTrackingNumber , $aShipmentItemsCollectionData);
  }

  public function getFetchrUrl(){

      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

    $fetchr_url_tracking = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue('fetchr_config_main/fetchr_config/fetchr_tracking_url');

            return $fetchr_url_tracking;


  }

  public function getFetchrToken(){

      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

    $fetchr_tracking_token = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue('fetchr_config_main/fetchr_config/fetchr_tracking_token');

            return $fetchr_tracking_token;


  }

  public function getCurlFetchrDetails($tracking_number){

    $token = $this->getFetchrToken();

    $url= $this->getFetchrUrl().$tracking_number;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    "Authorization: $token")
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($result , true);
    return $result;
  }

}
