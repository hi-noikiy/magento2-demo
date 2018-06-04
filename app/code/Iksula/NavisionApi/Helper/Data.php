<?php
/**
 * Copyright © 2016 Oscprofessionals® All Rights Reserved.
 */
namespace Iksula\NavisionApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data.
 */
class Data extends AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    private $_transportBuilder;
    protected $_categoryFactory;
    protected $_productCollectionFactory;
    protected $_productRepositoryFactory;
    protected $inventory_master = 'inventorymaster';
    protected $inventory_reservation = 'inventoryreservation';
    protected $inventory_posting = 'inventoryposting';
    protected $return_request = 'returnrequest';
    private $Request_parameters = array();
    private $default_storecode = 'MRDIF';
    public $InventoryPostingStatus = array('Partial' => 'PARTIAL' , 'closed' => 'CLOSED' , 'cancel_and_post' => 'CANCEL_AND_POST' , 'new_and_post' => 'NEW_AND_POST' , 'unreserve_and_post' => 'UNRESERVE_AND_POST');
    protected $_bannerFactory;
        protected $_categoryHelper;
    protected $_categoryRepository;
    protected $inventory_url_master = 'http://apt01.corp.apntbs.com:7046/APnTServiceAPI/api/ServiceAPIController/GetInventoryAndPriceMasterData';

    protected $inventory_reservation_url = 'http://apt01.corp.apntbs.com:7046/APnTServiceAPI/api/ServiceAPIController/InventoryReservation';

    protected $inventory_url_posting = 'http://apt01.corp.apntbs.com:7046/APnTServiceAPI/api/ServiceAPIController/InventoryPosting';

    protected $returnrequest_url = 'http://apt01.corp.apntbs.com:7046/APnTServiceAPI/api/ServiceAPIController/ReturnRequest';

    protected $storeinventoryFactory;

    protected $ordersplitfactory;

    protected $orderFactoryData;

    protected $orderitemFactoryData;

    protected $NavisionLogsmodel;

    protected $_logger;

    protected $storemanagerhelper;

    private $transfertolocation = 'WEBSTR';

    private $store_no = 'WEBSTR';

    protected $storemanagerModel;



    /**
     * Data constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ProductFactory  $productCollectionfactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory
        ,\Iksula\Storeinventory\Model\StoreinventoryFactory $storeinventoryFactory
        ,\Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitfactory
        ,\Magento\Sales\Model\OrderFactory    $orderFactoryData
        , \Magento\Sales\Model\Order\ItemFactory $orderitemFactoryData
        ,\Iksula\NavisionApi\Model\Navisionapi $NavisionLogsmodel
        ,\Psr\Log\LoggerInterface $logger
        ,\Iksula\Storemanager\Helper\Data $storemanagerhelper
        ,\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
        ,\Iksula\Storemanager\Model\StoremanagerFactory $storemanagerModel
    )
    {
        $this->_storeManager = $storeManager;
        $this->_categoryFactory = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionfactory;
        $this->storeinventoryFactory = $storeinventoryFactory;
        $this->ordersplitfactory = $ordersplitfactory;
        $this->orderFactoryData = $orderFactoryData;
        $this->orderitemFactoryData = $orderitemFactoryData;
        $this->NavisionLogsmodel = $NavisionLogsmodel;
        $this->_logger = $logger;
        $this->storemanagerhelper = $storemanagerhelper;
        $this->_transportBuilder = $transportBuilder;
        $this->storemanagerModel = $storemanagerModel;
    }


    public function getUrlbyMethod($method){

            switch($method){

                  case 'inventorymaster':
                    return array($this->inventory_url_master , 'GET');
                    break;
                  case 'inventoryreservation':
                        $InventoryReservationData = array($this->inventory_reservation_url , 'PUT');
                      return $InventoryReservationData;
                    break;
                    case 'inventoryposting':
                    $InventoryPostingData = array($this->inventory_url_posting , 'PUT');
                      return $InventoryPostingData;
                      break;
                      case 'returnrequest':
                       $ReturnRequestData = array($this->returnrequest_url , 'PUT');
                        return $ReturnRequestData;
                        break;

            }

    }


    public function getNavisionCall($method){


              list($url , $requestmethod) = $this->getUrlbyMethod($method);

              if($requestmethod == 'GET'){
                $result =   $this->getGetCurlRequest($url , $method);
              }elseif($requestmethod == 'PUT'){
                $aData = $this->Request_parameters;
                $result =   $this->getPutCurlRequest($url , $aData , $method);
              }

              return $result;

    }

    public function callInventoryReservationApi(){

          $this->getNavisionCall($this->inventory_reservation);
    }


    public function callInventoryPostingApi(){

      $this->getNavisionCall($this->inventory_posting);

    }

    public function callReturnRequestApi(){

    $aStoreInventory = $this->getNavisionCall($this->return_request);

    }


     public function getInventoryReservationData($Ordersplitid , $store_id){

        $aRequestData = array();

            $ordersplitidObject = $this->ordersplitfactory->create()->load($Ordersplitid);
            $orderItemsData = json_decode($ordersplitidObject->getOrderItemsData() , true);

            $order_id = $ordersplitidObject->getOrderId();
            $orderobj = $this->orderFactoryData->create()->load($order_id);
            $order_incrementid = $orderobj->getIncrementId(); // SOReferenceNo arguments
            $orderspiltid = $ordersplitidObject->getOrderItemId(); // SONumber
            $i = 0 ;
              foreach($orderItemsData as $ItemsData){
                  $aRequestData [$i] ['SOReferenceNo']= $order_incrementid;
                  $aRequestData [$i] ['SONumber']= $orderspiltid;
                  //$aRequestData [$i] ['StoreNo']= $store_id;
                  $aRequestData [$i] ['StoreNo']= $this->store_no;;
                  $aRequestData [$i] ['ItemNo']= $ItemsData['sku'];
                  $aRequestData [$i] ['IQuantity']= $ItemsData['inventory'];
                  $aRequestData [$i] ['ReservedQuantity']= $ItemsData['inventory'];
                  $aRequestData [$i] ['order_item_id'] = $ItemsData['order_items_id'];
                  $i++;
              }


              foreach($aRequestData as $key => $aRequestDataValues){

                        $order_items_id = $aRequestDataValues['order_item_id'];
                        $OrderItemsIdObj = $this->orderitemFactoryData->create()->load($order_items_id);
                        unset($aRequestData[$key]['order_item_id']);
                        $selling_price = ($OrderItemsIdObj->getBaseOriginalPrice() - $OrderItemsIdObj->getPriceInclTax());
                        $final_disc_amt = ($OrderItemsIdObj->getDiscountAmount() + $selling_price);

                        //$aRequestData [$key] ['DiscountValue'] = (int)$OrderItemsIdObj->getDiscountAmount();
                        $aRequestData [$key] ['DiscountValue'] = (int)$final_disc_amt;
                        //$aRequestData [$key] ['SubTotal'] = (int)($OrderItemsIdObj->getBaseOriginalPrice() *  $aRequestData[$key]['IQuantity']);
                        $aRequestData [$key] ['SubTotal'] = (int)(round($OrderItemsIdObj->getRowTotalInclTax()));
                        //$aRequestData [$key] ['SubTotal'] = (int)($OrderItemsIdObj->getBasePrice() *  $aRequestData[$key]['IQuantity']);
                        //$aRequestData [$key] ['Total'] = (int)($aRequestData [$key] ['SubTotal'] + $aRequestData [$key] ['DiscountValue']);
                        $aRequestData [$key] ['Total'] = (int)(round($aRequestData [$key] ['SubTotal']));
                        //$aRequestData [$key] ['NetPrice'] = (int)$OrderItemsIdObj->getBaseOriginalPrice();
                        $aRequestData [$key] ['NetPrice'] = (int)(round($OrderItemsIdObj->getPriceInclTax()));
              }

            return $aRequestData;


    }

    public function SendRequestParameters($aRequestData){


          $this->Request_parameters = $aRequestData;

    }


   /* public function getInventoryPostingData($Ordersplitid , $InventoryPostingStatus , $store_id){
        // $postingdata = array('SOReferenceNo' => 'WO0007' , 'SONumber' => 'SO0007' , 'StoreNo' => 'ALGUR' , 'TransferFromLocation' => '890' , 'ItemNo' => '1000' , 'IQuantity' => '1000' , 'ReservedQuantity' => '80' , 'SubTotal' => '1000' , 'Total' => '10000' , 'Status' => '1000');

        $aRequestData = array();

        $ordersplitidObject = $this->ordersplitfactory->create()->load($Ordersplitid);
        $orderItemsData = json_decode($ordersplitidObject->getOrderItemsData() , true);

        $order_id = $ordersplitidObject->getOrderId();
        $orderobj = $this->orderFactoryData->create()->load($order_id);
        $order_incrementid = $orderobj->getIncrementId(); // SOReferenceNo arguments
        $order_couponcode = $orderobj->getCouponCode(); 
        // echo "<pre>";
        // print_r($orderobj->getData());
        // echo "aaa"; exit;
        $order_total_qty_ordered = (int)$orderobj->getData('total_qty_ordered'); 
        $order_discount_amount = (int)$orderobj->getData('discount_amount'); 
        $discountPerItem = $order_discount_amount/$order_total_qty_ordered;
        


        $orderspiltid = $ordersplitidObject->getOrderItemId(); // SONumber
        $i = 0 ;
        foreach($orderItemsData as $ItemsData){
        $aRequestData [$i] ['SOReferenceNo']= $order_incrementid;
        $aRequestData [$i] ['SONumber']= $orderspiltid;
        $aRequestData [$i] ['StoreNo']= $this->store_no;
        $aRequestData [$i] ['TransferFromLocation']= $store_id;
        $aRequestData [$i] ['ItemNo']= $ItemsData['sku'];
        $aRequestData [$i] ['IQuantity']= $ItemsData['inventory'];
        $aRequestData [$i] ['ReservedQuantity']= $ItemsData['inventory'];
        $aRequestData [$i] ['order_item_id'] = $ItemsData['order_items_id'];
        $i++;
        }


        foreach($aRequestData as $key => $aRequestDataValues){

            $order_items_id = $aRequestDataValues['order_item_id'];
            $OrderItemsIdObj = $this->orderitemFactoryData->create()->load($order_items_id);
            
            $order_qtyordered =  $OrderItemsIdObj->getQtyOrdered();
           
            unset($aRequestData[$key]['order_item_id']);
            // $aRequestData [$key] ['DiscountValue'] = (int)$OrderItemsIdObj->getDiscountAmount();
            if(isset($order_couponcode)&& $order_couponcode!=""){
              $aRequestData [$key] ['DiscountValue'] = abs((int)$discountPerItem*(int)$order_qtyordered);
            }else{
              $aRequestData [$key] ['DiscountValue'] = (int)$OrderItemsIdObj->getBaseOriginalPrice()-(int)$OrderItemsIdObj->getBasePrice();                          
            }
            $aRequestData [$key] ['SubTotal'] = (int)($OrderItemsIdObj->getBaseOriginalPrice() *  $aRequestData[$key]['IQuantity']);
            $aRequestData [$key] ['Total'] = (int)($aRequestData [$key] ['SubTotal'] + $aRequestData [$key] ['DiscountValue']);
            // unset($aRequestData [$key] ['DiscountValue']);
          //  $aRequestData [$key] ['NetPrice'] = (int)$OrderItemsIdObj->getBaseOriginalPrice();
            $aRequestData [$key] ['Status'] = $InventoryPostingStatus;
        }

        // echo "aaaa<pre>";
        // print_r($aRequestData);
        //  exit;
        return $aRequestData;

    }*/

    public function getInventoryPostingData($Ordersplitid , $InventoryPostingStatus , $store_id){
            // $postingdata = array('SOReferenceNo' => 'WO0007' , 'SONumber' => 'SO0007' , 'StoreNo' => 'ALGUR' , 'TransferFromLocation' => '890' , 'ItemNo' => '1000' , 'IQuantity' => '1000' , 'ReservedQuantity' => '80' , 'SubTotal' => '1000' , 'Total' => '10000' , 'Status' => '1000');
            $aRequestData = array();
                $ordersplitidObject = $this->ordersplitfactory->create()->load($Ordersplitid);
                $orderItemsData = json_decode($ordersplitidObject->getOrderItemsData() , true);
                // $orderItemsData [0] ['sku'] =  'SKU-RS-3S';
                // $orderItemsData [0] ['inventory'] =  1;
                // $orderItemsData [0] ['order_items_id'] =  185;
                // $orderItemsData [1] ['sku'] =  'SKU-RS-3S';
                // $orderItemsData [1] ['inventory'] =  1;
                // $orderItemsData [1] ['order_items_id'] =  185;
                //
                //
                // echo '<pre>';
                // print_r($orderItemsData);
                // exit;
                $order_id = $ordersplitidObject->getOrderId();
                $orderobj = $this->orderFactoryData->create()->load($order_id);
                $order_couponcode = $orderobj->getCouponCode(); 
                 $order_total_qty_ordered = (int)$orderobj->getData('total_qty_ordered'); 
                $order_discount_amount = (int)$orderobj->getData('discount_amount'); 
                $discountPerItem = $order_discount_amount/$order_total_qty_ordered;
                $order_incrementid = $orderobj->getIncrementId(); // SOReferenceNo arguments
                $orderspiltid = $ordersplitidObject->getOrderItemId(); // SONumber
                $i = 1 ;
                $shipping_charges = 0;
                $shipping_charges_orderspiltid = 'Orderitems-'.$order_id.'-0';
                  foreach($orderItemsData as $ItemsData){

                      /*Start Add Shipping Charges SKU*/

                      if($shipping_charges == 1)
                      {
                          $aRequestData [$i - 1] ['SOReferenceNo']= $order_incrementid;
                          $aRequestData [$i - 1] ['SONumber']= $shipping_charges_orderspiltid;
                          $aRequestData [$i - 1] ['StoreNo'] = 'WEBSTR';
                          $aRequestData [$i - 1] ['TransferFromLocation'] = 'WEBSTR';
                          $aRequestData [$i - 1] ['ItemNo']= "4036";
                          $aRequestData [$i - 1] ['IQuantity']= '1';
                          $aRequestData [$i - 1] ['ReservedQuantity'] = '0';
                          $aRequestData [$i - 1] ['SubTotal'] = '20';

                          $aRequestData [$i - 1] ['shipping_charges']= 1;

                      }
                      
                      /*End Add Shipping Charges SKU*/

                      $aRequestData [$i] ['SOReferenceNo']= $order_incrementid;
                      $aRequestData [$i] ['SONumber']= $orderspiltid;
                      //$aRequestData [$i] ['StoreNo']= $store_id;
                      $aRequestData [$i] ['StoreNo']= $this->store_no;
                      //$aRequestData [$i] ['TransferFromLocation']= '-';
                      //$aRequestData [$i] ['TransferFromLocation']= $this->transfertolocation;
                      $aRequestData [$i] ['TransferFromLocation']= $store_id;
                      $aRequestData [$i] ['ItemNo']= $ItemsData['sku'];
                      $aRequestData [$i] ['IQuantity']= $ItemsData['inventory'];
                      $aRequestData [$i] ['ReservedQuantity']= $ItemsData['inventory'];
                      $aRequestData [$i] ['order_item_id'] = $ItemsData['order_items_id'];

                      $aRequestData [$i] ['shipping_charges']= 0;
                      $i++;
                  }
                  foreach($aRequestData as $key => $aRequestDataValues){
                    if($aRequestData [$key] ['shipping_charges'] == 1)
                            {
                              $aRequestData [$key] ['Total'] = "20";
                              
                              $aRequestData [$key] ['Status'] = "DEL_CHARGE";
                            }
                            else
                            {
                            $order_items_id = $aRequestDataValues['order_item_id'];
                            $OrderItemsIdObj = $this->orderitemFactoryData->create()->load($order_items_id);
                            unset($aRequestData[$key]['order_item_id']);
                            $order_qtyordered =  $OrderItemsIdObj->getQtyOrdered();
                            //$aRequestData [$key] ['DiscountValue'] = (int)$OrderItemsIdObj->getDiscountAmount();
                            if(isset($order_couponcode)&& $order_couponcode!=""){
                              $aRequestData [$key] ['DiscountValue'] = abs((int)$discountPerItem*(int)$order_qtyordered);
                            }else{
                              $aRequestData [$key] ['DiscountValue'] = (int)$OrderItemsIdObj->getBaseOriginalPrice()-(int)$OrderItemsIdObj->getBasePrice();                          
                            }
                            $aRequestData [$key] ['SubTotal'] = (int)($OrderItemsIdObj->getBaseOriginalPrice() *  $aRequestData[$key]['IQuantity']);
                            //$aRequestData [$key] ['SubTotal'] = (int)($OrderItemsIdObj->getBasePrice() *  $aRequestData[$key]['IQuantity']);
                            $aRequestData [$key] ['Total'] = (int)($aRequestData [$key] ['SubTotal'] + $aRequestData [$key] ['DiscountValue']);
                            unset($aRequestData [$key] ['DiscountValue']);
                          //  $aRequestData [$key] ['NetPrice'] = (int)$OrderItemsIdObj->getBaseOriginalPrice();
                            $aRequestData [$key] ['Status'] = $InventoryPostingStatus;
                            }
                            unset($aRequestData [$key] ['shipping_charges']);
                  }
                return $aRequestData;
    }


    public function getRequestReturnData($OrderData = array()){

            // $returnrequestdata = array('SOReferenceNo' => 'WO0007' , 'SONumber' => 'SO0007' , 'StoreNo' => 'ALGUR' , 'ItemNo' => '1000' , 'IQuantity' => '1000' , 'TotalAmt' => '80' );

            $sku = $OrderData['product_sku'];
            $Quantity = $OrderData['quantity'];
            //$return_price = $OrderData['return_price'];

            $OrderIncrementId = $OrderData['order_id'];
            $Order_id = $this->orderFactoryData->create()->loadByIncrementId($OrderIncrementId)->getId();

            $OrderItemsData = $this->orderitemFactoryData->create()->getCollection()->addFieldToFilter('order_id' , array('eq' => $Order_id))->addFieldToFilter('sku' , array('eq' => $sku))->getData();

            //$return_price = round($OrderItemsData[0]['base_price']); //26
            //$tax_amount = round($OrderItemsData[0]['base_tax_amount']); //5

            //$base_original_price = round($OrderItemsData[0]['base_original_price']);
            $base_original_price = round($OrderItemsData[0]['price_incl_tax']);

            //$tax_amount_single = ($tax_amount/$Quantity); // 5/3 = 2

            //$total_amount = ($return_price + $tax_amount_single) * $Quantity; // (26 + 2) * 3

            $total_amount = ($base_original_price) * ($Quantity);



            $ordersplitidObject = $this->ordersplitfactory
                                      ->create()
                                      ->getCollection()
                                      ->addFieldToFilter('order_id' , array('eq' => $Order_id))
                                      ->getData();

                                      if(!empty($ordersplitidObject)){
                                            $OrderItemsSplitId =      $ordersplitidObject[0]['order_item_id'];
                                            $store_id = $ordersplitidObject[0]['allocated_storeids'];
                                            //$store_code = $this->storemanagerhelper->getStoreCodeByStoreId($store_id);
                                            $store_code = $this->store_no;

                                      }else{
                                            $OrderItemsSplitId = '-';
                                            //$store_code = $this->default_storecode;
                                            $store_code = $this->store_no;
                                      }

            $aData = array('SOReferenceNo' => $OrderIncrementId , 'SONumber' => $OrderItemsSplitId , 'StoreNo' => $store_code , 'ItemNo' => $sku , 'IQuantity' => $Quantity , 'TotalAmt' => $total_amount );


            return $aData;


    }


    // public function getInventoryPostingData(){
    //
    //         $postingdata = array('SOReferenceNo' => 'WO0007' , 'SONumber' => 'SO0007' , 'StoreNo' => 'ALGUR' , 'TransferFromLocation' => '890' , 'ItemNo' => '1000' , 'IQuantity' => '1000' , 'ReservedQuantity' => '80' , 'SubTotal' => '1000' , 'Total' => '10000' , 'Status' => '1000');
    //
    //         $postingApiUrlAppend = implode('/' , $postingdata);
    //
    //         return array($postingdata , $postingApiUrlAppend);
    //
    //
    // }
    //
    // public function getRequestReturnData(){
    //
    //         $returnrequestdata = array('SOReferenceNo' => 'WO0007' , 'SONumber' => 'SO0007' , 'StoreNo' => 'ALGUR' , 'ItemNo' => '1000' , 'IQuantity' => '1000' , 'TotalAmt' => '80' );
    //
    //         $returnrequestApUrlAppend = implode('/' , $returnrequestdata);
    //
    //         return array($returnrequestdata , $returnrequestApUrlAppend);
    //
    //
    // }

    public function getGetCurlRequest($url , $APimethod){

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      $result = curl_exec($ch);
      curl_close($ch);
      $result_decode = json_decode($result , true);
      $this->SaveInventoryFile($result);
      return $result_decode;
    }


    public function getPutCurlRequest($url , $aData , $APimethod){
      $aRequestData = $aData;
      // if($APimethod == 'returnrequest'){
      //     $aData['ItemNo'] = '1000';
      //       $DataAppended = implode('/' , $aData);
      // }else{

        $DataAppended = implode('/' , $aData);
      //}

      $url = $url . '/' . $DataAppended;
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
      curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($aRequestData));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      $result = curl_exec($ch);
      curl_close($ch);
      $result_decode = json_decode($result , true);

      /***** Write a Logs for Navision Api in Logs ********/
      $sData = json_encode($aData);
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Navision.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
           $logger->info("Method :- ". $APimethod . "<=======> Request Data :-  ". $sData . "<========>" );
            $logger->info("Response Data :- ". $result);


          //   $DataLogs = array('method_name'=> $APimethod ,'request'=> $sData ,'response'=> $result, 'request_datetime' =>  date('m/d/Y h:i:s a') , 'response_datetime' =>  date('m/d/Y h:i:s a'));
          //   $model = $this->NavisionLogsmodel->create()->setData($DataLogs);
          // try {
          //         $insertId = $model->save()->getId();
          //     } catch (Exception $e){
          //      echo $e->getMessage();
          // }
      /*************************/

      return $result_decode;
    }


      function SaveInventoryFile($result){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
        $mediaPath=$fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
        $customfolder = 'storeinventorymaster_dump/';
        $filename = 'inventory';
        $csv_filename = $mediaPath .$customfolder. $filename."_".date("Y-m-d_H-i",time()).".csv";
        file_put_contents($csv_filename,$result);
      }


    public function OutStockAllProducts(){

         $productCollection = $this->_productCollectionFactory
                                 ->create()
                                 ->getCollection()
                                 ->getData();

                                 foreach($productCollection as $productData){

                                   try{
                                     $objectManager1 = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                                      $resource1 = $objectManager1->get('Magento\Framework\App\ResourceConnection');
                                      $connection1 = $resource1->getConnection();
                                      $tableName1 = 'cataloginventory_stock_item'; //gives table name with prefix

                                      //Delete Data from table
                                      $sql1 = "UPDATE ".$tableName1 ." set qty= 0  , is_in_stock = 0 where product_id = ". $productData['entity_id'];

                                      $connection1->query($sql1);


                                      $objectManager2 = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                                       $resource2 = $objectManager2->get('Magento\Framework\App\ResourceConnection');
                                       $connection2 = $resource2->getConnection();
                                       $tableName2 = 'cataloginventory_stock_status'; //gives table name with prefix

                                       //Delete Data from table
                                       $sql2 = "UPDATE ".$tableName2 ." set qty = 0 , stock_status = 0  where product_id = ". $productData['entity_id'];

                                       $connection2->query($sql2);

                                   }catch(Exception $e){
                                     echo $e->getMessage();
                                   }

                                 }
    }


    public function callInventoryMasterApi(){


       $aStoreListing = $this->getStoreCodeList();
       //$aStoreListing = array('MRDIF');


      //
             $aStoreInventory = $this->getNavisionCall($this->inventory_master);


             // $aStoreInventory = array(
             // array('sku' => 'S22222' , 'storeNo' => array('MRDIF') , 'iInventory' => array(10) , 'originalPrice' =>  array(200.00) , 'ecomercePrice'  => array(0) , 'bufferInventory' => array(10)) ,
             // array('sku' => 'Test1' , 'storeNo' => array('MRDIF') , 'iInventory' => array(20) , 'originalPrice' => array(100.00) , 'ecomercePrice'  => array(50.00) , 'bufferInventory' => array(0)),
             // array('sku' => 'S22222' , 'storeNo' => array('MRDIF') , 'iInventory' => array(10) , 'originalPrice' => array(100.00) , 'ecomercePrice'  => array(0.00) , 'bufferInventory' => array(0)),
             // array('sku' => 'Evonne Dinner Set' , 'storeNo' => array('MRDIF1') , 'iInventory' => array(20) , 'originalPrice' => array(100.00) , 'ecomercePrice'  => array(0.00) , 'bufferInventory' => array(0)));

            // echo '<pre>';
            // print_r($aStoreInventory);
            //exit;

            $iOnlineInventoryPrice = array();

              if(!empty($aStoreInventory)){

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                 $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                 $connection = $resource->getConnection();
                 $tableName = 'storeinventory_tb'; //gives table name with prefix

                 //Delete Data from table
                 $sql = "Truncate table " . $tableName;
                 $connection->query($sql);

               foreach($aStoreInventory as $key => $storeData){

                    $iSku = trim($storeData['sku']);
                    $wholeinventory = (int)$storeData['iInventory'][0];
                    $wholeinventory = ($wholeinventory < 0 ? 0 : $wholeinventory);
                    $bufferinventory = (int)$storeData['bufferInventory'][0];
                    $bufferinventory = ($bufferinventory < 0 ? 0 : $bufferinventory);
                    $original_price = (int)$storeData['originalPrice'][0];
                    $original_price = ($original_price < 0 ? 0 : $original_price);
                    $ecomm_price = (int)$storeData['ecomercePrice'][0];
                    $ecomm_price = ($ecomm_price < 0 ? 0 : $ecomm_price);
                    $store_code = $storeData['storeNo'][0];
                    $ValidProductQuantity = ($bufferinventory > $wholeinventory ?  false : true);
                    $ValidProductPrice = ($ecomm_price > $original_price ? false : true);


                    if(!in_array($store_code , $aStoreListing)){
                      continue;
                    }
                      $productLoadCheck = $this->_productCollectionFactory->create()->loadByAttribute('sku' , $iSku);

                      if(empty($productLoadCheck)){
                             continue;
                      }



                    if (array_key_exists($iSku,$iOnlineInventoryPrice))
                     {
                                   $OldInventory = $iOnlineInventoryPrice[$iSku]['inventory'];

                                   if(!$ValidProductQuantity){

                                    $NewInventory = $OldInventory +  0 ;

                                   }else{
                                    $NewInventory = $OldInventory +  ($wholeinventory - $bufferinventory) ;
                                   }

                                   $iOnlineInventoryPrice[$iSku]['inventory'] = $NewInventory;

                                   if(!$ValidProductPrice){
                                            $iOnlineInventoryPrice[$iSku]['original_price'] =  $original_price;
                                            $iOnlineInventoryPrice[$iSku]['ecomm_price'] =  $original_price;
                                   }else{
                                        if(($ecomm_price != 0)){
                                            $iOnlineInventoryPrice[$iSku]['original_price'] =  $original_price;
                                            $iOnlineInventoryPrice[$iSku]['ecomm_price'] =  $ecomm_price;
                                          }else{
                                            $iOnlineInventoryPrice[$iSku]['original_price'] =  $original_price;
                                            $iOnlineInventoryPrice[$iSku]['ecomm_price'] =  $original_price;
                                          }
                                   }


                     }else{
                                   if(!$ValidProductQuantity){

                                    $NewInventory =  0 ;
                                   }else{
                                    $NewInventory = ($wholeinventory - $bufferinventory) ;
                                   }

                                   $iOnlineInventoryPrice [$iSku]['inventory'] = $NewInventory;

                                   if(!$ValidProductPrice){
                                            $iOnlineInventoryPrice[$iSku]['original_price'] =  $original_price;
                                            $iOnlineInventoryPrice[$iSku]['ecomm_price'] =  $original_price;
                                   }else{
                                        if(($ecomm_price != 0)){
                                            $iOnlineInventoryPrice[$iSku]['original_price'] =  $original_price;
                                            $iOnlineInventoryPrice[$iSku]['ecomm_price'] =  $ecomm_price;
                                          }else{
                                            $iOnlineInventoryPrice[$iSku]['original_price'] =  $original_price;
                                            $iOnlineInventoryPrice[$iSku]['ecomm_price'] =  $original_price;
                                          }
                                   }


                     }


                       $data = array('sku'=>$iSku,'store_id'=> $store_code ,'original_price'=> $original_price
                               , 'ecomm_price' => $ecomm_price , 'buffer_inventory' => $bufferinventory, 'inventory' => $wholeinventory);
                     $model = $this->storeinventoryFactory->create()->setData($data);
                     try {
                             $insertId = $model->save()->getId();
                             //echo "Data successfully inserted. Insert ID: ".$insertId.'<br />';
                         } catch (Exception $e){
                          echo $e->getMessage();
                     }


               }


           }

           if(!empty($iOnlineInventoryPrice)){
             $this->OutStockAllProducts();
             foreach($iOnlineInventoryPrice as  $sku => $dataValues){
                   if($dataValues['inventory'] > 0){
                       $stockData = 1;
                   }else{
                     $stockData = 0 ;
                   }
                         $productLoad = $this->_productCollectionFactory->create()->loadByAttribute('sku' , $sku);

                         if(!empty($productLoad)){
                           try{
                             $objectManager1 = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                              $resource1 = $objectManager1->get('Magento\Framework\App\ResourceConnection');
                              $connection1 = $resource1->getConnection();
                              $tableName1 = 'cataloginventory_stock_item'; //gives table name with prefix

                              //Delete Data from table
                              $sql1 = "UPDATE ".$tableName1 ." set qty = ".$dataValues['inventory']." , is_in_stock = ".$stockData." where product_id = ". $productLoad->getId();

                              $connection1->query($sql1);

                              /************ MRP Price update for Product *********/


                              $objectManager11 = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                               $resource11 = $objectManager11->get('Magento\Framework\App\ResourceConnection');
                               $connection11 = $resource11->getConnection();
                               $tableName11 = 'catalog_product_entity_decimal'; //gives table name with prefix

                               //Delete Data from table
                               $sql11 = "UPDATE ".$tableName11 ." set value = ".$dataValues['original_price']." where attribute_id = 77 and  entity_id = ". $productLoad->getId();

                               $connection11->query($sql11);


                               /*************************************************/


                              $objectManager2 = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                               $resource2 = $objectManager2->get('Magento\Framework\App\ResourceConnection');
                               $connection2 = $resource2->getConnection();
                               $tableName2 = 'cataloginventory_stock_status'; //gives table name with prefix

                               //Delete Data from table
                               $sql2 = "UPDATE ".$tableName2 ." set qty = ".$dataValues['inventory']." , stock_status = ".$stockData."  where product_id = ". $productLoad->getId();

                               $connection2->query($sql2);


                               /*************** Update special Price **************/


                               $objectManager21 = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                                $resource21 = $objectManager21->get('Magento\Framework\App\ResourceConnection');
                                $connection21 = $resource21->getConnection();
                                $tableName21 = 'catalog_product_entity_decimal'; //gives table name with prefix

                                //Delete Data from table
                                $sql21 = "UPDATE ".$tableName21 ." set value = ".$dataValues['ecomm_price']." where attribute_id = 78 and  entity_id = ". $productLoad->getId();

                                $connection21->query($sql21);


                                /**********************/

                           }catch(Exception $e){
                             echo $e->getMessage();
                            }

                         }else{
                           echo $sku .' dont exist';
                           echo '<br />';
                         }

             }
             $this->inventoryDumpMail();
           }

    }

    public function getStoreCodeList(){

        $aStoreCodeCollection = $this->storemanagerModel->create()->getCollection()
                        ->addFieldToSelect('store_code')
                        ->getData();

            foreach($aStoreCodeCollection as $key => $aStoreCodeValues){
                  $aStoreCodeData [] = $aStoreCodeValues['store_code'];
            }

            return $aStoreCodeData;
    }

    public function inventoryDumpMail()
    {
            //$emailids = 'firospk@2xlme.com,vishal.p@iksula.com';
            $emailids = 'vishal.p@iksula.com';
            $emailids = explode(',', $emailids);
            $transport = $this->_transportBuilder->setTemplateIdentifier('storeinventory_mail_template')
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
                ->setTemplateVars([
                    'store'     => 1,
                    ])
                ->setFrom('general')
                ->addTo($emailids)
                ->getTransport();
            $transport->sendMessage();
    }


}
