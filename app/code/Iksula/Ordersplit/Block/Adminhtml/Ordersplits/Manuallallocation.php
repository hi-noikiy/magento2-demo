<?php

namespace Iksula\Ordersplit\Block\Adminhtml\Ordersplits;

class Manuallallocation extends \Magento\Backend\Block\Template
{

    protected $ordersplitFactory;
    protected $storeinventoryFactory;
    protected $storemanagerFactory;
    protected $OrderSplitHelper;

    public function __construct( \Magento\Backend\Block\Template\Context $context
                                ,  \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
                                , \Iksula\Storeinventory\Model\StoreinventoryFactory $storeinventoryFactory
                                , \Iksula\Storemanager\Model\StoremanagerFactory $storemanagerFactory
                                , \Iksula\Ordersplit\Helper\Data   $OrderSplitHelper
                                ){


        $this->ordersplitFactory = $ordersplitFactory;
        $this->storeinventoryFactory = $storeinventoryFactory;
        $this->storemanagerFactory = $storemanagerFactory;
        $this->OrderSplitHelper = $OrderSplitHelper;
         parent::__construct($context);

    }

    public function getOrderItemRowId(){

        $order_itemsids = $this->getRequest()->getParam('order_itemrowid');

        return $order_itemsids;

    }


    public function getStoreDataBySkuQty($sku , $qty , $order_unique_id){

        $qty = (int)$qty;
        $aStoreManagerCollection = array();
        $storeInventoryData = $this->storeinventoryFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('sku' , array('eq' => $sku))
                                    ->addFieldToFilter('inventory' , array('gteq' => $qty))
                                    ->getData();



                                $aRejectedStoreids = $this->OrderSplitHelper->getStoreIdsRejected($order_unique_id);

                     foreach($storeInventoryData as $storeData){

                            $storemanagerData = $this->storemanagerFactory->create()
                                                     ->load( $storeData['store_id'] , 'store_code')
                                                     ->getData();

                                     if(in_array($storemanagerData['storemanager_id'], $aRejectedStoreids)){
                                        continue;
                                     }

                              $aStoreManagerCollection []   = array( 'name' => $storemanagerData['store_name'] , 'storemanager_id' => $storemanagerData['storemanager_id'] , 'qty' => $storeData ['inventory']);
                     }


                     return $aStoreManagerCollection;

    }


    public function getCollectionsOrderSplits($orderitemsid)
    {

        $OrderSplitsCollection = array();

        $orderitemsidsData = $this->ordersplitFactory->create()->getCollection()->addFieldToFilter('id' , array('eq' => $orderitemsid))->getData();


        foreach($orderitemsidsData as $orderitemsvalues){

            $OrderSplitsCollection  = array('order_items_data' => $orderitemsvalues['order_items_data'] , 'allocated_storeids' => $orderitemsvalues['allocated_storeids'] , 'order_item_id' => $orderitemsvalues['order_item_id'] , 'order_id' => $orderitemsvalues['order_id']);

        }
        // $store_code = 'store-2';
        // $Emailids = $this->OrderSplitHelper->sendPicklistToWarehouse($store_code);
        // echo '<pre>';
        // print_r($Emailids);
        // exit;

        return $OrderSplitsCollection;
    }

}
