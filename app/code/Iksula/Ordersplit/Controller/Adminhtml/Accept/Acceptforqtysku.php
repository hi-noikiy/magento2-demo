<?php
namespace Iksula\Ordersplit\Controller\Adminhtml\Accept;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class  Acceptforqtysku extends Action
{


    protected $OrderSplitHelper;
    protected $ordersplitFactory;
    protected $storeinventoryFactory;
    protected $storemanagerfactory;
    protected $storemanagerhelper;
    protected $orderitemFactoryData;


    public function __construct(Context $context
                                , \Iksula\Ordersplit\Helper\Data  $OrderSplitHelper
                                , \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
                                ,\Iksula\Storeinventory\Model\StoreinventoryFactory $storeinventoryFactory
                                ,\Iksula\Storemanager\Model\StoremanagerFactory $storemanagerfactory
                                ,\Iksula\Storemanager\Helper\Data $storemanagerhelper
                                 , \Magento\Sales\Model\Order\ItemFactory $orderitemFactoryData
                                ) {

        $this->OrderSplitHelper    = $OrderSplitHelper;
        $this->ordersplitFactory = $ordersplitFactory;
        $this->storeinventoryFactory = $storeinventoryFactory;
        $this->storemanagerfactory = $storemanagerfactory;
        $this->storemanagerhelper = $storemanagerhelper;
        $this->orderitemFactoryData = $orderitemFactoryData;
        parent::__construct($context);
    }

    public function execute()
    {

        $order_items_data = $this->getRequest()->getPost('order_items_data');
        $split_items_check = $this->getRequest()->getPost('split_items');
        $allocated_storeid = $this->getRequest()->getPost('allocated_id');

        $accptedOrderItemsData = array();
        $splitOrderItemsData = array();
        $i = 0 ;
        $order_items_data_entries = array();

        foreach($order_items_data as $key => $orderItemsValue){
          $row_id = $orderItemsValue['row_id'];
          $inventorycheckstoreid = true;
          if($orderItemsValue['allocated_storeid'] != ""){
            $store_code = $this->storemanagerhelper->getStoreCodeByStoreId($orderItemsValue['allocated_storeid']);

            $inventorycheckstoreid = $this->OrderSplitHelper->checkforacceptedQty($store_code , $orderItemsValue['sku'] , $orderItemsValue['inventory']);
          }
            if((!$inventorycheckstoreid) || ($orderItemsValue['inventory'] == 0)){

                $action_status = 'lessinventory_accepted_store';
                $inventorysetup = $orderItemsValue['original_qty'];
            }else{

                $action_status = 'accept_action';
                $inventorysetup = $orderItemsValue['inventory'];
            }
              /********* If Split Items Success then Spilt row and store in table ***********/
            if($split_items_check == 'true'){
              $order_items_data_entries = array();


                $order_items_data_entries  [] = array('sku' => $orderItemsValue['sku'] , 'inventory' => $inventorysetup , 'order_items_id' => $orderItemsValue['order_items_id']);

                $allocated_id = $orderItemsValue['allocated_storeid'];

                if($i == 0){

                $this->updateFirstOrderItems($row_id , $order_items_data_entries , $allocated_id , $action_status);
                }else{

                  $this->OrderSplitHelper->splitOrderInOrderSplitTable($order_items_data_entries , $allocated_id , $action_status);
                }
/*********************************************************************************************************/

            }else{

                $order_items_data_entries  [] = array('sku' => $orderItemsValue['sku'] , 'inventory' => $inventorysetup , 'order_items_id' => $orderItemsValue['order_items_id']);

            }

            $i++;
        }

        if($split_items_check == 'false'){

            $this->updateFirstOrderItems($row_id , $order_items_data_entries , $allocated_storeid , $action_status);

        }

        $result ['error'] = 0;
        $result ['result_content'] = "Data is updated";

        echo json_encode($result);


    }


    public function updateFirstOrderItems($orderrowid , $OrderitemsData , $allocatedStoreId , $action_status){


                    $SOrderitemsData = json_encode($OrderitemsData);

                    if($action_status == 'accept_action'){ // Conditions when Action is accepted by the store for specific sku's

                      if( trim($allocatedStoreId) != ""){ //Store is accepted only if store allocated is not empty otherwise ordersplit is unallocated status

                        $order_item_status = 'store_accepted';
                        $store_code = $this->storemanagerhelper->getStoreCodeByStoreId($allocatedStoreId);

                        $this->OrderSplitHelper->deductInventoryIfAccept($OrderitemsData , $store_code);

                      }else{
                          $order_item_status = 'store_unallocated';
                      }


                    }elseif($action_status == 'lessinventory_accepted_store'){


                      if( trim($allocatedStoreId) != ""){

                        $order_item_status = 'store_allocated';
                      }else{
                          $order_item_status = 'store_unallocated';
                      }

                    }

                    try{

                    $ordersplitModel = $this->ordersplitFactory->create()->load($orderrowid);
                    $ordersplitModel->setOrderItemsData($SOrderitemsData);
                    $ordersplitModel->setOrderItemStatus($order_item_status);
                    $ordersplitModel->setAllocatedStoreids($allocatedStoreId);
                    $ordersplitModel->save();
                  }
                  catch(Expection $e){

                      echo $e->getMessage();

                  }

                  if($action_status == 'accept_action'){  // Send the Picklist to warehouse when the store accept the order items

                        foreach($OrderitemsData as $aOrderItemsValues){

                            $order_item_id = $aOrderItemsValues['order_items_id'];
                            break;
                        }

                        if($order_item_status == 'store_accepted'){  // Call the picklist mail to warehouse if store id is not null and order is unallocated.

                          $aOrderItemData  = $this->orderitemFactoryData->create()->load($order_item_id);
                          $order_id = $aOrderItemData->getOrderId();

                              $order_item_id = $this->ordersplitFactory->create()->load($orderrowid)->getOrderItemId();
                              $picklist_mail_status = $this->OrderSplitHelper->sendPicklistToWarehouse($store_code , $order_item_id , $orderrowid , $order_id);

                              if($picklist_mail_status){

                                $ordersplitModelforPicklistUpdate = $this->ordersplitFactory->create()->load($orderrowid);
                                $ordersplitModelforPicklistUpdate->setPicklistSent(1);
                                $ordersplitModelforPicklistUpdate->save();
                              }
                        }
                  }
    }



}
