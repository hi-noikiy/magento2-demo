<?php
namespace Iksula\Ordersplit\Controller\Adminhtml\Accept;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class  Picklistmailsend extends Action
{
    protected $OrderSplitHelper;
    protected $ordersplitFactory;
    protected $storeinventoryFactory;
    protected $storemanagerfactory;
    protected $storemanagerhelper;


    public function __construct(Context $context
                                , \Iksula\Ordersplit\Helper\Data  $OrderSplitHelper
                                , \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
                                ,\Iksula\Storeinventory\Model\StoreinventoryFactory $storeinventoryFactory
                                ,\Iksula\Storemanager\Model\StoremanagerFactory $storemanagerfactory
                                ,\Iksula\Storemanager\Helper\Data $storemanagerhelper
                                ) {

        $this->OrderSplitHelper    = $OrderSplitHelper;
        $this->ordersplitFactory = $ordersplitFactory;
        $this->storeinventoryFactory = $storeinventoryFactory;
        $this->storemanagerfactory = $storemanagerfactory;
        $this->storemanagerhelper = $storemanagerhelper;
        parent::__construct($context);
    }

    public function execute()
    {

        $order_row_itemid = $this->getRequest()->getParam('order_itemrowid');
        $ordersplitObj = $this->ordersplitFactory->create()->load($order_row_itemid);
        $store_id = $ordersplitObj->getAllocatedStoreids();
        $store_code = $this->storemanagerhelper->getStoreCodeByStoreId($store_id);
        $ordersplit_item_id = $ordersplitObj->getOrderItemId();
        $order_id = $ordersplitObj->getOrderId();

        try{
        $picklist_mail_status = $this->OrderSplitHelper->sendPicklistToWarehouse($store_code , $ordersplit_item_id , $order_row_itemid , $order_id);

        if($picklist_mail_status){
          $ordersplitModelforPicklistUpdate = $this->ordersplitFactory->create()->load($order_row_itemid);
          $ordersplitModelforPicklistUpdate->setPicklistSent(1);
          $ordersplitModelforPicklistUpdate->save();

          $result ['error'] = 0;
          $result ['result_content'] = "Picklist mail is sent";
        }else{

          $result ['error'] = 1;
          $result ['result_content'] = "Picklist mail sending fail";

        }
      }catch(Exception $e){
        $result ['error'] = 1;
        $result ['result_content'] = $e->getMessage();
      }

        echo json_encode($result);
    }


}
