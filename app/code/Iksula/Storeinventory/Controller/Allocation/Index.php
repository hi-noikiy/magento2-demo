<?php
namespace Iksula\Storeinventory\Controller\Allocation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;


class Index extends Action
{
    protected $storemanagerFactory;
    protected $storeinventoryFactory;
    protected $ordersplitFactory;
    protected $orderFactoryData;
    protected $orderItemsFactoryData;
    protected $OrderSplitHelper;
    private $_transportBuilder;

    public function __construct(
                                Context $context
                                , \Iksula\Storeinventory\Model\StoreinventoryFactory $storeinventoryFactory
                                , \Iksula\Storemanager\Model\StoremanagerFactory $storemanagerFactory
                                , \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
                                , \Magento\Sales\Model\OrderFactory    $orderFactoryData
                                ,\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
                                , \Magento\Sales\Model\Order\ItemFactory     $orderItemsFactoryData
                                , \Iksula\Ordersplit\Helper\Data   $OrderSplitHelper
                                )
    {
        $this->orderItemsFactoryData = $orderItemsFactoryData;
        $this->storemanagerFactory = $storemanagerFactory;
        $this->storeinventoryFactory = $storeinventoryFactory;
        $this->ordersplitFactory  = $ordersplitFactory;
        $this->orderFactoryData    = $orderFactoryData;
        $this->_transportBuilder = $transportBuilder;
        $this->OrderSplitHelper    = $OrderSplitHelper;

        parent::__construct($context);
    }



    public function getStoreIdByStoreCode($storecode){

        $storeid = $this->storemanagerFactory->create()->load($storecode , 'store_code')->getStoremanagerId();

        return $storeid;

    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $time_start = microtime(true);

        $orderCollection = $this->orderFactoryData->create()->getCollection()
                            ->addFieldToFilter('state' , array('eq' => 'processing'))
                            //->addFieldToFilter('entity_id' , array('eq' => 34))
                            ->addFieldToFilter('ordersplit_status' , array('eq' => 0));


        foreach($orderCollection as $orderData){

            $orderData = $this->orderFactoryData->create()->load($orderData->getId());
            $orderItems = $orderData->getAllItems();
            $aOrderItemsData = array();

            foreach($orderItems as $orderitemsData){

                $aOrderItemsData [] = array('sku' => $orderitemsData->getSku() , 'inventory' => $orderitemsData->getQtyOrdered() , 'order_items_id' => $orderitemsData->getItemId());

            }

            $orderShippingAddress = $orderData->getShippingAddress();
            $orderregionid = $orderShippingAddress->getRegionId();

            $this->allocateOrderNearestWarehouse($orderregionid , $aOrderItemsData);


        }

        $time_end = microtime(true);
        $time = $time_end - $time_start;

        $this->storeinventoryAllocationMail($time);

    }

    public function storeinventoryAllocationMail($time)
    {   
            //$emailids = 'firospk@2xlme.com,vishal.p@iksula.com';
            $emailids = 'vishal.p@iksula.com';
            $emailids = explode(',', $emailids);
            $transport = $this->_transportBuilder->setTemplateIdentifier('storeinventory_mail_template')
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
                ->setTemplateVars([
                    'message'     => "Storeinventory Allocation Cron Run Time",
                    'time'      => $time,
                    ])
                ->setFrom('general')
                ->addTo($emailids)
                ->getTransport();
            $transport->sendMessage();
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
                                        $warehousenotstoresplit = false;

                                        if(!empty($store_warehouseCollection)){


                                            $StoreInventoryCollection  = $this->storeinventoryFactory->create()
                                            ->getCollection()
                                            ->addFieldToFilter('sku', array('eq' => $sOrderItems['sku']))
                                            ->addFieldToFilter('store_id', array('in' => $Warehouse_storecode))
                                            ->addFieldToFilter('inventory',array('gteq' => (int)$sOrderItems['inventory']))
                                            ->getData();
                                        }else{
                                          $warehousenotstoresplit = true;
                                            $warehousenot_ordersplit [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);
                                            //break;
                                        }

                                        if(!empty($StoreInventoryCollection)){


                                            foreach($StoreInventoryCollection as $StoreCodeData){

                                                $storeid = $this->getStoreIdByStoreCode($StoreCodeData['store_id']);

                                                break;
                                            }

                                                $warehouse_orderspilt [$storeid][] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);


                                        }else{

                                          //Need to check this changes
                                            if(!$warehousenotstoresplit) // Variable is not true then only make a entry otherwise don't entry will be replicated.
                                                $warehousenot_ordersplit [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);
                                        }

                                    }

                                    if(!empty($warehouse_orderspilt)){

                                      foreach($warehouse_orderspilt as $storeid => $OrdersplitData){

                                        $this->OrderSplitHelper->splitOrderInOrderSplitTable($OrdersplitData , $storeid, 'manualallocation_action');


                                       }
                                        // $this->OrderSplitHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $storeid , 'manualallocation_action');


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

                      $warehousenotstoresplit = false;

                        if(!empty($store_notwarehouseCollection)){

                                        $StoreInventoryCollection  = $this->storeinventoryFactory->create()
                                        ->getCollection()
                                        ->addFieldToFilter('sku', array('eq' => $sOrderItems['sku']))
                                        ->addFieldToFilter('store_id', array('in' => $aStorecode))
                                        ->addFieldToFilter('inventory',array('gteq' => (int)$sOrderItems['inventory']))
                                        ->getData();



                        }else{
                          $warehousenotstoresplit = true;
                            $store_notorderspilt [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);
                                //break;
                        }

                                        if(!empty($StoreInventoryCollection)){


                                            foreach($StoreInventoryCollection as $StoreCodeData){

                                                $storeid = $this->getStoreIdByStoreCode($StoreCodeData['store_id']);
                                                break;
                                            }

                                                $store_orderspilt [$storeid][] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);

                                        }else{
                                        //  Need to check
                                        if(!$warehousenotstoresplit) // Variable is not true then only make a entry otherwise don't entry will be replicated.
                                                $store_notorderspilt [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);

                                        }
                    }



                    if(!empty($store_orderspilt)){

                      foreach($store_orderspilt as $storeid => $OrdersplitData){

                        $this->OrderSplitHelper->splitOrderInOrderSplitTable($OrdersplitData , $storeid, 'manualallocation_action');


                       }
                        // $this->OrderSplitHelper->splitOrderInOrderSplitTable($store_orderspilt , $storeid , 'manualallocation_action');
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
                      $warehousenotstoresplit = false;

                                if(!empty($store_warehouseCollection)){



                                        $StoreInventoryCollection  = $this->storeinventoryFactory->create()
                                        ->getCollection()
                                        ->addFieldToFilter('sku', array('eq' => $sOrderItems['sku']))
                                        ->addFieldToFilter('store_id', array('in' => $aStorecode))
                                        ->addFieldToFilter('inventory',array('gteq' => (int)$sOrderItems['inventory']))
                                        ->getData();

                                    }else{

                                        $warehousenotstoresplit = true;
                                        $store_notorderspilt [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items' => $sOrderItems['order_items_id']);
                                        //break;
                                    }


                                        if(!empty($StoreInventoryCollection)){

                                                foreach($StoreInventoryCollection as $StoreCodeData){

                                                    $storeid = $this->getStoreIdByStoreCode($StoreCodeData['store_id']);

                                                    break;
                                                }

                                                $store_orderspilt [$storeid][] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);

                                        }else{

                                          //Need to check
                                            if(!$warehousenotstoresplit) // Variable is not true then only make a entry otherwise don't entry will be replicated.
                                                $store_notorderspilt [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);
                                        }

                                        // if(!empty($store_orderspilt)){
                                        //
                                        //
                                        //                 $this->OrderSplitHelper->splitOrderInOrderSplitTable($store_orderspilt , $storeid, 'manualallocation_action');
                                        // }
                    }



                    if(!empty($store_orderspilt)){



                      foreach($store_orderspilt as $storeid => $OrdersplitData){
                        //
                        // echo '<pre>';
                        // print_r($OrdersplitData);
                        // exit;

                        $this->OrderSplitHelper->splitOrderInOrderSplitTable($OrdersplitData , $storeid, 'manualallocation_action');


                       }


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
                                      $warehousenotstoresplit = false;

                                        if(!empty($store_warehouseCollection)){

                                            $StoreInventoryCollection  = $this->storeinventoryFactory->create()
                                            ->getCollection()
                                            ->addFieldToFilter('sku', array('eq' => $sOrderItems['sku']))
                                            ->addFieldToFilter('store_id', array('in' => $Warehouse_storecode))
                                            ->getData();

                                        }else{
                                          $warehousenotstoresplit = true;
                                            $warehousenot_ordersplit [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);
                                          //  break;

                                        }

                                        if(!empty($StoreInventoryCollection)){

                                            foreach($StoreInventoryCollection as $StoreCodeData){

                                                $storeid = $this->getStoreIdByStoreCode($StoreCodeData['store_id']);
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

                                                        $this->OrderSplitHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');


                                                        $remainginventoryqty = ($sOrderItems['inventory'] - $storeInventory);
                                                    }elseif(($sOrderItems['inventory'] > $storeInventory )&& ($remainginventoryqty > 0) && ($storeInventory != 0)){

                                                      $warehouse_orderspilt = array();

                                                      $warehouse_orderspilt  []= array('sku' => $sOrderItems['sku'] , 'inventory' => $storeInventory , 'order_items_id' => $sOrderItems['order_items_id']);

                                                      $this->OrderSplitHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');

                                                    }elseif(($sOrderItems['inventory'] == $storeInventory )&& ($remainginventoryqty == 0)){

                                                      $warehouse_orderspilt = array();

                                                      $warehouse_orderspilt  []= array('sku' => $sOrderItems['sku'] , 'inventory' => $storeInventory , 'order_items_id' => $sOrderItems['order_items_id']);

                                                      $this->OrderSplitHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');

                                                    }
                                                    $i++;
                                                }

                                            }

                                        }else{

                                                  if(!$warehousenotstoresplit) // Variable is not true then only make a entry otherwise don't entry will be replicated.
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
                                      $warehousenotstoresplit = false;

                                        if(!empty($store_warehouseCollection)){

                                            $StoreInventoryCollection  = $this->storeinventoryFactory->create()
                                            ->getCollection()
                                            ->addFieldToFilter('sku', array('eq' => $sOrderItems['sku']))
                                            ->addFieldToFilter('store_id', array('in' => $Warehouse_storecode))
                                            ->getData();

                                        }else{
                                          $warehousenotstoresplit = true;
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
                                                $storeid = $this->getStoreIdByStoreCode($StoreCodeData['store_id']);
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


                                                        $this->OrderSplitHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');


                                                    }elseif(($sOrderItems['inventory'] > $storeInventory )&& ($remainginventoryqty > 0) && ($storeInventory != 0)){

                                                      $warehouse_orderspilt = array();

                                                      $warehouse_orderspilt  []= array('sku' => $sOrderItems['sku'] , 'inventory' => $storeInventory , 'order_items_id' => $sOrderItems['order_items_id']);


                                                      $this->OrderSplitHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');

                                                    }elseif(($sOrderItems['inventory'] == $storeInventory )&& ($remainginventoryqty == 0)){

                                                      $warehouse_orderspilt = array();

                                                      $warehouse_orderspilt  []= array('sku' => $sOrderItems['sku'] , 'inventory' => $storeInventory , 'order_items_id' => $sOrderItems['order_items_id']);


                                                      $this->OrderSplitHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');

                                                    }
                                                    $i++;
                                                }

                                            }



                                        }else{
                                            if(!$warehousenotstoresplit) // Variable is not true then only make a entry otherwise don't entry will be replicated.
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
                                      $warehousenotstoresplit = false;

                                        if(!empty($store_warehouseCollection)){



                                            $StoreInventoryCollection  = $this->storeinventoryFactory->create()
                                            ->getCollection()
                                            ->addFieldToFilter('sku', array('eq' => $sOrderItems['sku']))
                                            ->addFieldToFilter('store_id', array('in' => $Warehouse_storecode))
                                            ->getData();

                                        }else{
                                            $warehousenotstoresplit = true;
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
                                                $storeid = $this->getStoreIdByStoreCode($StoreCodeData['store_id']);
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




                                                        $this->OrderSplitHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');


                                                    }elseif(($sOrderItems['inventory'] > $storeInventory ) && ($remainginventoryqty > 0) && ($storeInventory != 0)){

                                                      $warehouse_orderspilt = array();

                                                      $warehouse_orderspilt  []= array('sku' => $sOrderItems['sku'] , 'inventory' => $storeInventory , 'order_items_id' => $sOrderItems['order_items_id']);



                                                      $this->OrderSplitHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');

                                                    }elseif(($sOrderItems['inventory'] == $storeInventory )&& ($remainginventoryqty == 0)){

                                                      $warehouse_orderspilt = array();

                                                      $warehouse_orderspilt  []= array('sku' => $sOrderItems['sku'] , 'inventory' => $storeInventory , 'order_items_id' => $sOrderItems['order_items_id']);



                                                      $this->OrderSplitHelper->splitOrderInOrderSplitTable($warehouse_orderspilt , $store_id, 'manualallocation_action');

                                                    }
                                                    $i++;
                                                }

                                            }



                                        }else{
                                          if(!$warehousenotstoresplit) // Variable is not true then only make a entry otherwise don't entry will be replicated.
                                                $warehousenot_ordersplit [] = array('sku' => $sOrderItems['sku'] , 'inventory' => $sOrderItems['inventory'] , 'order_items_id' => $sOrderItems['order_items_id']);
                                        }

                                    }


                                    if(!empty($warehousenot_ordersplit)){



                                            //$this->splitOrderInOrderSplitTable($warehousenot_ordersplit , '');
                                        $this->OrderSplitHelper->splitOrderInOrderSplitTable($warehousenot_ordersplit , '', 'manualallocation_action');


                                    }


                }



                function sortByInventory($storeDataInventory){

                    asort($storeDataInventory);
                    return $storeDataInventory;

                }


}
