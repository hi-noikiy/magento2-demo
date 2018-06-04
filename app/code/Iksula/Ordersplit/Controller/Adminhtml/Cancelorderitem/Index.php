<?php
namespace Iksula\Ordersplit\Controller\Adminhtml\Cancelorderitem;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class  Index extends Action
{


    protected $OrderSplitHelper;
    protected $ordersplitFactory;
    protected $storeinventoryFactory;
    protected $storemanagerfactory;
    protected $storemanagerhelper;
    protected $orderitemFactoryData;
    protected $productCollectionfactory;


    public function __construct(Context $context
                                , \Iksula\Ordersplit\Helper\Data  $OrderSplitHelper
                                , \Magento\Catalog\Model\ProductFactory  $productCollectionfactory
                                , \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
                                ,\Iksula\Storeinventory\Model\StoreinventoryFactory $storeinventoryFactory
                                ,\Iksula\Storemanager\Model\StoremanagerFactory $storemanagerfactory
                                ,\Iksula\Storemanager\Helper\Data $storemanagerhelper
                                 , \Magento\Sales\Model\Order\ItemFactory $orderitemFactoryData
                                 ,PageFactory $resultPageFactory,
                                 \Magento\Framework\App\Request\Http $request
                                ) {
                                  parent::__construct($context);
                                  $this->resultPageFactory = $resultPageFactory;
                                  $this->request = $request;
        $this->OrderSplitHelper    = $OrderSplitHelper;
        $this->ordersplitFactory = $ordersplitFactory;
        $this->storeinventoryFactory = $storeinventoryFactory;
        $this->storemanagerfactory = $storemanagerfactory;
        $this->storemanagerhelper = $storemanagerhelper;
        $this->orderitemFactoryData = $orderitemFactoryData;
        $this->productCollectionfactory = $productCollectionfactory;

    }

    public function execute()
    {


      $aOrderItemStatusReverseUpdate = array('store_accepted' , 'store_invoiced' , 'store_shipped');
      $ordersplit_id = $this->getRequest()->getParam('ordersplitid');


        $orderitemsplitObj = $this->ordersplitFactory->create()->load($ordersplit_id);
        $OrderItemsData = json_decode($orderitemsplitObj->getOrderItemsData() , true);
        $OrdersplitStatus = $orderitemsplitObj->getOrderItemStatus();


        if(in_array($OrdersplitStatus , $aOrderItemStatusReverseUpdate)){

          $allocated_id = $orderitemsplitObj->getAllocatedStoreids();
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

                                $productLoad = $this->productCollectionfactory->create()->loadByAttribute('sku' , $storeinventory['sku']);

                                if(!empty($productLoad)){
                                /*************** Get Qty of Product *****************/
                                  $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                                  $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
                                  $availableqtyproduct = $StockState->getStockQty($productLoad->getId(), $productLoad->getStore()->getWebsiteId());
                                  /**********************************************/

                                  $iUpdatedQtyproduct = ($availableqtyproduct) + ($OrderItemsInventory);
                                  $stockData = ($iUpdatedQtyproduct > 0 ? 1 : 0);
                                  $productLoad->setStockData(['qty' => $iUpdatedQtyproduct, 'is_in_stock' => $stockData]);
                                  $productLoad->setQuantityAndStockStatus(['qty' => $iUpdatedQtyproduct, 'is_in_stock' => $stockData]);
                                  $productLoad->save();
                                  
                                }

                              try{
                                $storeinventoryUpdate = $this->storeinventoryFactory->create()
                                                              ->load($storeinventory['id'])
                                                              ->setInventory($iUpdatedInventory)
                                                              ->save();
                              }catch(Exception $e){
                                $this->messageManager->addError( __($e->getMessage()));
                                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                                return $resultRedirect;
                              }
                          }

                      }
              }

      }


        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
      try{
        $ordersplitModel = $this->ordersplitFactory->create()->load($ordersplit_id);
        $ordersplitModel->setOrderItemStatus('store_cancelled');
        $ordersplitModel->save();

        $this->messageManager->addSuccess( __('Order Items has been cancelled.') );
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
      }catch(Exception $e){
        $this->messageManager->addError( __($e->getMessage()));
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
      }

    }

}
