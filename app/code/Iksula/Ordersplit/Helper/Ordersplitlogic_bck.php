<?php

namespace Iksula\Ordersplit\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;


class Ordersplitlogic extends AbstractHelper
{

  protected $orderFactoryData;
  protected $ordersplitFactory;
  protected $orderitemFactoryData;
  protected $rejectionFactory;
  protected $storeinventoryFactory;
  protected $storemanagerhelper;
  protected $storemanagerFactory;
  protected $directoryListinterface;
  protected $baseurl;
  protected $invoiceFactory;
  protected $addressFactory;
  protected $productfactory;
  protected $storescopeInterface;
  protected $ordersplitDataHelper;



  public function __construct( \Magento\Sales\Model\OrderFactory    $orderFactoryData
                               , \Magento\Sales\Model\Order\ItemFactory $orderitemFactoryData
                              , \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
                              , \Iksula\Ordersplit\Model\RejectionFactory $rejectionFactory
                              ,\Iksula\Storeinventory\Model\StoreinventoryFactory $storeinventoryFactory
                              ,\Iksula\Storemanager\Helper\Data $storemanagerhelper
                              ,\Iksula\Storemanager\Model\StoremanagerFactory $storemanagerFactory
                              ,\Magento\Framework\App\Filesystem\DirectoryList $DirectoryListInterface
                              ,\Magento\Store\Model\StoreManagerInterface $baseurl
                              ,\Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory
                              ,\Magento\Sales\Model\Order\AddressFactory $addressFactory
                              ,\Magento\Catalog\Model\ProductFactory $productfactory
                              ,\Magento\Store\Model\StoreManagerInterface $storescopeInterface
                              ,\Iksula\Ordersplit\Helper\Data $ordersplitDataHelper
                              ){

      $this->orderFactoryData = $orderFactoryData;
      $this->ordersplitFactory = $ordersplitFactory;
      $this->orderitemFactoryData = $orderitemFactoryData;
      $this->rejectionFactory = $rejectionFactory;
      $this->storeinventoryFactory = $storeinventoryFactory;
      $this->storemanagerhelper = $storemanagerhelper;
      $this->storemanagerFactory = $storemanagerFactory;
      $this->directoryListinterface = $DirectoryListInterface;
      $this->baseurl = $baseurl;
      $this->invoiceFactory = $invoiceFactory;
      $this->addressFactory = $addressFactory;
      $this->productfactory = $productfactory;
      $this->storescopeInterface = $storescopeInterface;
      $this->ordersplitDataHelper = $ordersplitDataHelper;

  }


public function OrdersplitOfOrders($sales_order_id){



      $sales_order_data = $this->orderFactoryData->create()->load($sales_order_id);
      $orderItems = $sales_order_data->getAllItems();
      $aOrderItemsData = array();

      foreach($orderItems as $orderitemsData){

          $aOrderItemsData [] = array('sku' => $orderitemsData->getSku() , 'inventory' => $orderitemsData->getQtyOrdered() , 'order_items_id' => $orderitemsData->getItemId());

      }

      $orderShippingAddress = $sales_order_data->getShippingAddress();
      $orderregionid = $orderShippingAddress->getRegionId();
      $this->allocateOrderNearestWarehouse($orderregionid , $aOrderItemsData);


}


function allocateOrderNearestWarehouse($region_id , $aOrderItemsData){


    $warehouse_orderspilt = array();


    $store_warehouseCollection = $this->storemanagerFactory->create()->getCollection()
                                            ->addFieldToFilter('store_type' , array('eq' => 'warehouse'))
                                            ->addFieldToFilter('store_state' , array('eq' => $region_id))
                                            ->addFieldToFilter('store_status' , array('eq' => 1))
                                            ->getData();

                                            if(!empty($store_warehouseCollection)){

                                                    foreach($store_warehouseCollection as $storeData){

                                                        $Warehouse_storecode []= $storeData['store_code'];
                                                        $Warehouse_storeid []= $storeData['storemanager_id'];

                                                    }
                                            }

                                foreach($aOrderItemsData as $sOrderItems){

                                    if(!empty($store_warehouseCollection)){


                                        $StoreInventoryCollection  = $this->storeinventoryFactory->create()
                                        ->getCollection()
                                        ->addFieldToFilter('sku', array('eq' => $sOrderItems['sku']))
                                        ->addFieldToFilter('store_id', array('in' => $Warehouse_storecode))
                                        ->addFieldToFilter('inventory',array('gteq' => (int)$sOrderItems['inventory']))
                                        ->getData();
                                    }else{
                                        $warehousenot_ordersplit [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);
                                        break;
                                    }

                                    if(!empty($StoreInventoryCollection)){

                                        foreach($StoreInventoryCollection as $StoreCodeData){

                                            $storeid = $this->storemanagerhelper->getStoreIdByStoreCode($StoreCodeData['store_id']);

                                            break;
                                        }



                                            $warehouse_orderspilt [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);


                                    }else{
                                            $warehousenot_ordersplit [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);
                                    }

                                }

                                if(!empty($warehouse_orderspilt)){

                                    $this->ordersplitDataHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $storeid , 'manualallocation_action');

                                }

                                if(!empty($warehousenot_ordersplit)){


                                        $this->allocateneareststore($warehousenot_ordersplit , $region_id);

                                }
        }


            function allocateneareststore($orderItemsData , $region_id){


                $store_orderspilt = array();
                $store_notorderspilt = array();
                $aStorecode = array();

                $store_notwarehouseCollection = $this->storemanagerFactory->create()->getCollection()
                                            ->addFieldToFilter('store_type' , array('neq' => 'warehouse'))
                                            ->addFieldToFilter('store_state' , array('eq' => $region_id))
                                            ->addFieldToFilter('store_status' , array('eq' => 1))
                                            ->getData();



                            if(!empty($store_notwarehouseCollection)){

                                foreach($store_notwarehouseCollection as $storeData){

                                    $aStorecode []= $storeData['store_code'];

                                }
                            }


                foreach($orderItemsData as $sOrderItems){

                    if(!empty($store_notwarehouseCollection)){

                                    $StoreInventoryCollection  = $this->storeinventoryFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('sku', array('eq' => $sOrderItems['sku']))
                                    ->addFieldToFilter('store_id', array('in' => $aStorecode))
                                    ->addFieldToFilter('inventory',array('gteq' => (int)$sOrderItems['inventory']))
                                    ->getData();



                    }else{



                            $store_notorderspilt [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);

                            break;


                    }

                                    if(!empty($StoreInventoryCollection)){


                                        foreach($StoreInventoryCollection as $StoreCodeData){

                                            $storeid = $this->storemanagerhelper->getStoreIdByStoreCode($StoreCodeData['store_id']);
                                            break;
                                        }

                                            $store_orderspilt [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);

                                    }else{
                                            $store_notorderspilt [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);

                                    }
                }

                if(!empty($store_orderspilt)){

                    $this->ordersplitDataHelper->splitOrderInOrderSplitTable($store_orderspilt , $storeid , 'manualallocation_action');

                }

                if(!empty($store_notorderspilt)){

                        $this->allocateotherstore($store_notorderspilt , $region_id);

                }

            }

            function allocateotherstore($orderItemsData , $region_id){

                $store_orderspilt = array();
                $store_notorderspilt = array();

                $store_warehouseCollection = $this->storemanagerFactory
                                            ->create()
                                            ->getCollection()
                                            ->addFieldToFilter('store_status' , array('eq' => 1))
                                            ->getData();


                                 if(!empty($store_warehouseCollection)){

                                    foreach($store_warehouseCollection as $storeData){

                                        $aStorecode []= $storeData['store_code'];

                                    }
                                }


                foreach($orderItemsData as $sOrderItems){

                            if(!empty($store_warehouseCollection)){

                                    $StoreInventoryCollection  = $this->storeinventoryFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('sku', array('eq' => $sOrderItems['sku']))
                                    ->addFieldToFilter('store_id', array('in' => $aStorecode))
                                    ->addFieldToFilter('inventory',array('gteq' => (int)$sOrderItems['inventory']))
                                    ->getData();

                                }else{

                                    $store_notorderspilt [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items' => $sOrderItems['order_items_id']);
                                    break;
                                }

                                    if(!empty($StoreInventoryCollection)){


                                            foreach($StoreInventoryCollection as $StoreCodeData){

                                                $storeid = $this->storemanagerhelper->getStoreIdByStoreCode($StoreCodeData['store_id']);

                                                break;
                                            }

                                            $store_orderspilt [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);

                                    }else{
                                            $store_notorderspilt [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);
                                    }
                }

                if(!empty($store_orderspilt)){



                                $this->ordersplitDataHelper->splitOrderInOrderSplitTable($store_orderspilt , $storeid, 'manualallocation_action');



                }

                if(!empty($store_notorderspilt)){

                        $this->allocateNearestWarehouseqtySplit($store_notorderspilt , $region_id);

                }

            }

            function allocateNearestWarehouseqtySplit($orderItemsData , $region_id){



                $warehouse_orderspilt = array();



                $store_warehouseCollection = $this->storemanagerFactory->create()->getCollection()
                                            ->addFieldToFilter('store_type' , array('eq' => 'warehouse'))
                                            ->addFieldToFilter('store_state' , array('eq' => $region_id))
                                            ->addFieldToFilter('store_status' , array('eq' => 1))
                                            ->getData();


                                            if(!empty($store_warehouseCollection)){

                                                    foreach($store_warehouseCollection as $storeData){

                                                        $Warehouse_storecode []= $storeData['store_code'];
                                                        $Warehouse_storeid []= $storeData['storemanager_id'];

                                                    }
                                            }

                                foreach($orderItemsData as $sOrderItems){

                                    if(!empty($store_warehouseCollection)){

                                        $StoreInventoryCollection  = $this->storeinventoryFactory->create()
                                        ->getCollection()
                                        ->addFieldToFilter('sku', array('eq' => $sOrderItems['sku']))
                                        ->addFieldToFilter('store_id', array('in' => $Warehouse_storecode))
                                        ->getData();

                                    }else{

                                        $warehousenot_ordersplit [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);
                                        break;

                                    }

                                    if(!empty($StoreInventoryCollection)){

                                        foreach($StoreInventoryCollection as $StoreCodeData){

                                            $storeid = $this->storemanagerhelper->getStoreIdByStoreCode($StoreCodeData['store_id']);
                                            $storeinventory = (int)$StoreCodeData['inventory'];
                                            $storeDataInventory [] = array($storeid => $storeinventory);
                                        }

                                        $this->sortByInventory($storeDataInventory);


                                        $storedatainventory = 0;
                                        foreach($storeDataInventory as $key => $values){

                                            foreach($values as $store_id => $inventory){

                                                $storedatainventory += $inventory;

                                            }

                                        }
                                        $remainedqtynotassigned = 0;

                                        if($sOrderItems['inventory'] > $storedatainventory){
                                            $remainedqtynotassigned =  $sOrderItems['inventory'] - $storedatainventory;
                                        }elseif($storedatainventory > $sOrderItems['inventory']){
                                            $remainedqtynotassigned = $storedatainventory - $sOrderItems['inventory'];
                                        }



                                        if($remainedqtynotassigned > 0){

                                                $warehousenot_ordersplit [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $remainedqtynotassigned , 'order_items_id' => $sOrderItems['order_items_id']);

                                        }

                                        $remainginventoryqty = 0 ;
                                        $i = 0;

                                        foreach($storeDataInventory as $key => $storeInventoryData){

                                            foreach($storeInventoryData as $store_id => $storeInventory ){

                                                if($i == 0){
                                                    $remainginventoryqty = $sOrderItems['inventory'] - $storeInventory;
                                                }

                                                if(($sOrderItems['inventory'] > $storeInventory) && ($remainginventoryqty > 0) && ($storeInventory != 0)){


                                                    $warehouse_orderspilt = array();

                                                    $warehouse_orderspilt  []= array('sku' => $sOrderItems['sku'] , 'inventory' => $storeInventory , 'order_items_id' => $sOrderItems['order_items_id']);


                                                    //$this->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id);

                                                    $this->ordersplitDataHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');


                                                    $remainginventoryqty = ($sOrderItems['inventory'] - $storeInventory);
                                                }elseif(($sOrderItems['inventory'] > $storeInventory )&& ($remainginventoryqty > 0) && ($storeInventory != 0)){

                                                  $warehouse_orderspilt = array();

                                                  $warehouse_orderspilt  []= array('sku' => $sOrderItems['sku'] , 'inventory' => $storeInventory , 'order_items_id' => $sOrderItems['order_items_id']);


                                                  $this->ordersplitDataHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');

                                                }elseif(($sOrderItems['inventory'] == $storeInventory )&& ($remainginventoryqty == 0)){

                                                  $warehouse_orderspilt = array();

                                                  $warehouse_orderspilt  []= array('sku' => $sOrderItems['sku'] , 'inventory' => $storeInventory , 'order_items_id' => $sOrderItems['order_items_id']);

                                                  $this->ordersplitDataHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');

                                                }
                                                $i++;
                                            }

                                        }

                                    }else{


                                            $warehousenot_ordersplit [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);


                                    }

                                }

                                if(!empty($warehousenot_ordersplit)){


                                   $this->allocateNearestStoresqtySplit($warehousenot_ordersplit , $region_id);

                                }

            }


            function allocateNearestStoresqtySplit($orderItemsData , $region_id){


                $warehouse_orderspilt = array();
              $warehousenot_ordersplit = array();



                $store_warehouseCollection = $this->storemanagerFactory->create()->getCollection()
                                            ->addFieldToFilter('store_type' , array('neq' => 'warehouse'))
                                            ->addFieldToFilter('store_state' , array('eq' => $region_id))
                                            ->addFieldToFilter('store_status' , array('eq' => 1))
                                            ->getData();


                                            if(!empty($store_warehouseCollection)){

                                                    foreach($store_warehouseCollection as $storeData){

                                                        $Warehouse_storecode []= $storeData['store_code'];
                                                        $Warehouse_storeid []= $storeData['storemanager_id'];

                                                    }
                                            }

                                foreach($orderItemsData as $sOrderItems){

                                    if(!empty($store_warehouseCollection)){



                                        $StoreInventoryCollection  = $this->storeinventoryFactory->create()
                                        ->getCollection()
                                        ->addFieldToFilter('sku', array('eq' => $sOrderItems['sku']))
                                        ->addFieldToFilter('store_id', array('in' => $Warehouse_storecode))
                                        ->getData();

                                    }else{

                                        $warehousenot_ordersplit [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);
                                        break;

                                    }

                                    if(!empty($StoreInventoryCollection)){


                                        $storeDataInventory = array();
                                        foreach($StoreInventoryCollection as $StoreCodeData){
                                          $storeinventoryvalue = (int)$StoreCodeData['inventory'];
                                          if($storeinventoryvalue == 0){
                                            continue;
                                          }
                                            $storeid = $this->storemanagerhelper->getStoreIdByStoreCode($StoreCodeData['store_id']);
                                            $storeinventory = $storeinventoryvalue;
                                            $storeDataInventory [] = array($storeid => $storeinventory);
                                        }

                                        $this->sortByInventory($storeDataInventory);




                                        $storedatainventory = 0;
                                        foreach($storeDataInventory as $key => $values){

                                            foreach($values as $store_id => $inventory){

                                                $storedatainventory += $inventory;

                                            }

                                        }


                                        $remainedqtynotassigned = 0 ;


                                        if($sOrderItems['inventory'] > $storedatainventory){
                                            $remainedqtynotassigned =  $sOrderItems['inventory'] - $storedatainventory;
                                        }elseif($storedatainventory > $sOrderItems['inventory']){
                                          $remainedqtynotassigned =  $storedatainventory - $sOrderItems['inventory'];
                                        }


                                        if(($sOrderItems['inventory'] > $storedatainventory )&& ($remainedqtynotassigned > 0)){

                                                $warehousenot_ordersplit [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $remainedqtynotassigned , 'order_items_id' => $sOrderItems['order_items_id']);


                                        }


                                        $remainginventoryqty = 0 ;
                                        $i = 0;

                                        foreach($storeDataInventory as $key => $storeInventoryData){

                                            foreach($storeInventoryData as $store_id => $storeInventory ){

                                                if($i == 0){

                                                  if($sOrderItems['inventory'] > $storedatainventory){
                                                      $remainginventoryqty =  $sOrderItems['inventory'] - $storedatainventory;
                                                  }elseif($storedatainventory > $sOrderItems['inventory']){
                                                      $remainginventoryqty = $storedatainventory - $sOrderItems['inventory'];
                                                  }


                                                }



                                              if(($storeInventory > $sOrderItems['inventory'] )&& ($remainginventoryqty > 0) && ($storeInventory != 0)){

                                                    $warehouse_orderspilt = array();

                                                    $warehouse_orderspilt  []= array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);


                                                    $this->ordersplitDataHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');


                                                }elseif(($sOrderItems['inventory'] > $storeInventory )&& ($remainginventoryqty > 0) && ($storeInventory != 0)){

                                                  $warehouse_orderspilt = array();

                                                  $warehouse_orderspilt  []= array('sku' => $sOrderItems['sku'] , 'inventory' => $storeInventory , 'order_items_id' => $sOrderItems['order_items_id']);


                                                  $this->ordersplitDataHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');

                                                }elseif(($sOrderItems['inventory'] == $storeInventory )&& ($remainginventoryqty == 0)){

                                                  $warehouse_orderspilt = array();

                                                  $warehouse_orderspilt  []= array('sku' => $sOrderItems['sku'] , 'inventory' => $storeInventory , 'order_items_id' => $sOrderItems['order_items_id']);


                                                  $this->ordersplitDataHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');

                                                }
                                                $i++;
                                            }

                                        }



                                    }else{
                                            $warehousenot_ordersplit [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);
                                    }

                                }


                                if(!empty($warehousenot_ordersplit)){

                                        $this->allocateOthersStoresqtySplit($warehousenot_ordersplit , $region_id);

                                }


            }


            function allocateOthersStoresqtySplit($orderItemsData , $region_id){

                $warehouse_orderspilt = array();
                $warehousenot_ordersplit = array();



                $store_warehouseCollection = $this->storemanagerFactory->create()->getCollection()
                                            ->addFieldToFilter('store_state' , array('neq' => $region_id))
                                            ->addFieldToFilter('store_status' , array('eq' => 1))
                                            ->getData();


                                            if(!empty($store_warehouseCollection)){

                                                    foreach($store_warehouseCollection as $storeData){

                                                        $Warehouse_storecode []= $storeData['store_code'];
                                                        $Warehouse_storeid []= $storeData['storemanager_id'];

                                                    }
                                            }

                                foreach($orderItemsData as $sOrderItems){

                                    if(!empty($store_warehouseCollection)){



                                        $StoreInventoryCollection  = $this->storeinventoryFactory->create()
                                        ->getCollection()
                                        ->addFieldToFilter('sku', array('eq' => $sOrderItems['sku']))
                                        ->addFieldToFilter('store_id', array('in' => $Warehouse_storecode))
                                        ->getData();

                                    }else{

                                        $warehousenot_ordersplit [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);
                                        break;

                                    }

                                    if(!empty($StoreInventoryCollection)){
                                        $storeDataInventory = array();
                                        foreach($StoreInventoryCollection as $StoreCodeData){
                                          $storeinventoryvalue = (int)$StoreCodeData['inventory'];
                                          if($storeinventoryvalue == 0){
                                            continue;
                                          }
                                            $storeid = $this->storemanagerhelper->getStoreIdByStoreCode($StoreCodeData['store_id']);
                                            $storeinventory = $storeinventoryvalue;
                                            $storeDataInventory [] = array($storeid => $storeinventory);
                                        }

                                        $this->sortByInventory($storeDataInventory);


                                        $storedatainventory = 0;
                                        foreach($storeDataInventory as $key => $values){

                                            foreach($values as $store_id => $inventory){

                                                $storedatainventory += $inventory;

                                            }

                                        }


                                        $remainedqtynotassigned = 0 ;
                                        if($sOrderItems['inventory'] > $storedatainventory){
                                            $remainedqtynotassigned =  $sOrderItems['inventory'] - $storedatainventory;
                                        }elseif($storedatainventory > $sOrderItems['inventory']){
                                          $remainedqtynotassigned =  $storedatainventory - $sOrderItems['inventory'];
                                        }

                                        if(($sOrderItems['inventory'] > $storedatainventory )&& ($remainedqtynotassigned > 0)){

                                                $warehousenot_ordersplit [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $remainedqtynotassigned , 'order_items_id' => $sOrderItems['order_items_id']);

                                        }


                                        $remainginventoryqty = 0 ;
                                        $i = 0;

                                        foreach($storeDataInventory as $key => $storeInventoryData){

                                            foreach($storeInventoryData as $store_id => $storeInventory ){

                                                if($i == 0){

                                                  if($sOrderItems['inventory'] > $storedatainventory){
                                                      $remainginventoryqty =  $sOrderItems['inventory'] - $storedatainventory;
                                                  }elseif($storedatainventory > $sOrderItems['inventory']){
                                                      $remainginventoryqty = $storedatainventory - $sOrderItems['inventory'];
                                                  }


                                                }

                                              if(($storeInventory > $sOrderItems['inventory'] )&& ($remainginventoryqty > 0) && ($storeInventory != 0)){

                                                    $warehouse_orderspilt = array();

                                                    $warehouse_orderspilt  []= array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);




                                                    $this->ordersplitDataHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');


                                                }elseif(($sOrderItems['inventory'] > $storeInventory ) && ($remainginventoryqty > 0) && ($storeInventory != 0)){

                                                  $warehouse_orderspilt = array();

                                                  $warehouse_orderspilt  []= array('sku' => $sOrderItems['sku'] , 'inventory' => $storeInventory , 'order_items_id' => $sOrderItems['order_items_id']);



                                                  $this->ordersplitDataHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');

                                                }elseif(($sOrderItems['inventory'] == $storeInventory )&& ($remainginventoryqty == 0)){

                                                  $warehouse_orderspilt = array();

                                                  $warehouse_orderspilt  []= array('sku' => $sOrderItems['sku'] , 'inventory' => $storeInventory , 'order_items_id' => $sOrderItems['order_items_id']);



                                                  $this->ordersplitDataHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');

                                                }
                                                $i++;
                                            }

                                        }



                                    }else{
                                            $warehousenot_ordersplit [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);
                                    }

                                }


                                if(!empty($warehousenot_ordersplit)){



                                        //$this->splitOrderInOrderSplitTable($warehousenot_ordersplit , '');
                                    $this->ordersplitDataHelper->splitOrderInOrderSplitTable($warehousenot_ordersplit , '', 'manualallocation_action');


                                }


            }



            function sortByInventory($storeDataInventory){

                asort($storeDataInventory);
                return $storeDataInventory;

            }



}
