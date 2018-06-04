<?php
namespace Iksula\Ordersplit\Observer;

class Observer implements \Magento\Framework\Event\ObserverInterface
{


    protected $ordersplitsfactory;
    protected $storemanagerhelper;
    protected $storeinventoryFactory;

  public function __construct(
                                \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitsfactory,
                                \Iksula\Storemanager\Helper\Data $storemanagerhelper,
                                \Iksula\Storeinventory\Model\StoreinventoryFactory $storeinventoryFactory
  ){
          $this->ordersplitsfactory = $ordersplitsfactory;
          $this->storemanagerhelper = $storemanagerhelper;
          $this->storeinventoryFactory = $storeinventoryFactory;
  }

  public function execute(\Magento\Framework\Event\Observer $observer)
  {
    $order= $observer->getData('order');
    $aOrderItemStatusReverseUpdate = array('store_accepted' , 'store_invoiced' , 'store_shipped');
    $OrderItemsSplitCollection = $this->ordersplitsfactory->create()
              ->getCollection()
              ->addFieldToFilter('order_id' , array('eq' => $order->getId()))
              ->getData();

                  foreach($OrderItemsSplitCollection as $OrderItemsSplit){

                    $OrderItemsData = json_decode($OrderItemsSplit['order_items_data'] , true);
                    $OrdersplitStatus = $OrderItemsSplit['order_item_status'];

                    /**********/
                      if(in_array($OrdersplitStatus , $aOrderItemStatusReverseUpdate)){

                        $allocated_id = $OrderItemsSplit['allocated_storeids'];
                        $store_code = $this->storemanagerhelper->getStoreCodeByStoreId($allocated_id);
                        foreach($OrderItemsData as $order_items){

                            $sku = $order_items['sku'];
                            $inventory = $order_items['inventory'];

                            $storeinventoryData = $this->storeinventoryFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('sku' , array('eq' => $sku))
                                    ->addFieldToFilter('store_id' , array('eq' => $store_code))
                                    ->getData();


                                    if(!empty($storeinventoryData)){

                                        foreach($storeinventoryData as $storeinventory){

                                            $iStoreInventory = $storeinventory['inventory'];
                                            $OrderItemsInventory = $inventory;
                                            $iUpdatedInventory = ($iStoreInventory) + ($OrderItemsInventory);
                                              // $productLoad = $this->productCollectionfactory->create()->loadByAttribute('sku' , $storeinventory['sku']);

                                              // if(!empty($productLoad)){
                                              // /*************** Get Qty of Product *****************/
                                              //   $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                                              //   $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
                                              //   $availableqtyproduct = $StockState->getStockQty($productLoad->getId(), $productLoad->getStore()->getWebsiteId());
                                              //   /**********************************************/
                                              //
                                              //   $iUpdatedQtyproduct = ($availableqtyproduct) + ($OrderItemsInventory);
                                              //   $stockData = ($iUpdatedQtyproduct > 0 ? 1 : 0);
                                              //   $productLoad->setStockData(['qty' => $iUpdatedQtyproduct, 'is_in_stock' => $stockData]);
                                              //   $productLoad->setQuantityAndStockStatus(['qty' => $iUpdatedQtyproduct, 'is_in_stock' => $stockData]);
                                              //   $productLoad->save();
                                              //
                                              // }

                                            try{
                                              $storeinventoryUpdate = $this->storeinventoryFactory->create()
                                                                            ->load($storeinventory['id'])
                                                                            ->setInventory($iUpdatedInventory)
                                                                            ->save();
                                            }catch(Exception $e){

                                              echo $e->getMessage();

                                            }
                                        }

                                    }
                            }

                    }
                    try{
                      $ordersplitModel = $this->ordersplitsfactory->create()->load($OrderItemsSplit['id']);
                      $ordersplitModel->setOrderItemStatus('store_cancelled');
                      $ordersplitModel->save();
                    }catch(Exception $e){
                        echo $e->getMessage();
                    }
                    /**********/
              }
     return $this;
  }
}
