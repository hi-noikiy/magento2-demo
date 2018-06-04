<?php
namespace Iksula\Ordersplit\Controller\Adminhtml\Manualallocation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class  Allocationordertootherstore extends Action
{


    protected $OrderSplitHelper;
    protected $ordersplitFactory;
    protected $storemanagerhelper;
    protected $emailidshelper;
    protected $orderitemFactoryData;
    protected $orderFactoryData;
    protected $scopeConfig;


    public function __construct(Context $context
                                , \Iksula\Ordersplit\Helper\Data  $OrderSplitHelper
                                , \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
                                ,\Iksula\Storemanager\Helper\Data $storemanagerhelper
                                ,\Iksula\EmailTemplate\Helper\Email $emailidshelper
                                , \Magento\Sales\Model\Order\ItemFactory $orderitemFactoryData
                                ,\Magento\Sales\Model\OrderFactory    $orderFactoryData
                                 , \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
                                ) {

        $this->OrderSplitHelper    = $OrderSplitHelper;
        $this->ordersplitFactory = $ordersplitFactory;
        $this->storemanagerhelper = $storemanagerhelper;
        $this->emailidshelper = $emailidshelper;
        $this->orderitemFactoryData = $orderitemFactoryData;
        $this->orderFactoryData = $orderFactoryData;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {

        $order_items_data = $this->getRequest()->getPost('order_items_data');


        $accptedOrderItemsData = array();
        $splitOrderItemsData = array();
        $i = 0 ;
        foreach($order_items_data as $key => $orderItemsValue){
            $order_items_data_entries = array();

            $order_items_data_entries  [] = array('sku' => $orderItemsValue['sku'] , 'inventory' => $orderItemsValue['inventory'] , 'order_items_id' => $orderItemsValue['order_items_id']);
            $allocated_id = $orderItemsValue['allocated_storeid'];

            if($i == 0){
              $this->updateFirstOrderItems($orderItemsValue['row_id'] , $order_items_data_entries , $allocated_id , 'manualallocation_action');
            }else{
              $this->OrderSplitHelper->splitOrderInOrderSplitTable($order_items_data_entries , $allocated_id , 'manualallocation_action');
            }

            $i++;
        }
        $result ['error'] = 0;
        $result ['result_content'] = "Data is updated";

        echo json_encode($result);
    }


    public function updateFirstOrderItems($orderrowid , $OrderitemsData , $allocatedStoreId , $action_status){


                    $SOrderitemsData = json_encode($OrderitemsData);

                    if($action_status == 'manualallocation_action'){

                      if( trim($allocatedStoreId) != ""){

                        $order_item_status = 'store_allocated';


                        /********  Send the Email using helper by jesni *************/
                        $allocated_storecode = $this->storemanagerhelper->getStoreCodeByStoreId($allocatedStoreId);
                        $EmailTemplatesData = array();
                        $count_items = count($OrderitemsData);
                        $j = 0;
                        foreach($OrderitemsData as $key => $OrderItemsValues){
                          $aOrderItemData  = $this->orderitemFactoryData->create()->load($OrderItemsValues['order_items_id']);
                          $emailTempVariables = array();
                          $order_id = $aOrderItemData->getOrderId();
                          $order_incrementid = $this->orderFactoryData->create()->load($order_id)->getIncrementId();
                             /* $EmailTemplatesData ['item_details'] = array('sku' => $OrderItemsValues['sku'] , 'qty' => $OrderItemsValues['inventory'] , 'store_code' => $allocated_storecode , 'order_id' => $order_id);*/

                           // $emailTempVariables['row_id'] = $orderrowid;
                            //$j++;
                        }

                        $emailTempVariables['row_id'] = $orderrowid;
                        $emailTempVariables['order_id'] = $order_incrementid;
                            /*echo "333============<pre>";
                            print_r($EmailTemplatesData);

                            echo "<pre>";
                            print_r($emailTempVariables);
                            exit;*/
                            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                            $scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
                            $domain_name =  $scopeConfig->getValue('sms_configuration/sms_setting/domain_name');
                            $domain_email_id =  $scopeConfig->getValue('sms_configuration/sms_setting/domain_email_id');

                            

                        $senderInfo = [
                            'name' => $domain_name,
                            'email' => $domain_email_id
                        ];
                        $storeobj = $this->storemanagerhelper->getStoreManagerObject($allocatedStoreId);
                            $store_name = $storeobj->getStoreName();
                            $store_emailid = $storeobj->getStoreEmailid();
                            $store_code = $storeobj->getStoreCode();

                        $receiverInfo = [
                            'name' => $store_name,
                            'email' => $store_emailid
                        ];
                        
                        $this->emailidshelper->emailTemplate('order_allocation' , $emailTempVariables ,$senderInfo,$receiverInfo,'','');

                        $this->emailidshelper->emailTemplate('order_allocation_admin' , $emailTempVariables , $senderInfo, $receiverInfo, '' , '');

                         $is_enable =  $this->scopeConfig->getValue('sms_configuration/sms_setting/enable_allocation');
                            $template_path =  $this->scopeConfig->getValue('sms_configuration/sms_setting/order_allocation');
                            $ordersplitId = $this->ordersplitFactory->create()->load($orderrowid)->getOrderItemId();
                            $data = array(
                            'order_id' => $order_incrementid,
                            'store_name' => $store_name,
                            'store_code' => $store_code,
                            'ordersplit_id' => $ordersplitId
                            );

                            $AdminNumber  = $this->scopeConfig->getValue('sms_configuration/sms_setting/admin_number');
                            $storeNumber = $storeobj->getStoreMobileno();
                            $aNumber = array($AdminNumber , $storeNumber);
                            
                            if($is_enable)
                                $this->emailidshelper->smsTemplate($template_path, $data, $aNumber);

                        /*******************************/

                      }else{
                          $order_item_status = 'store_unallocated';
                      }

                    }

                    $ordersplitModel = $this->ordersplitFactory->create()->load($orderrowid);
                    $ordersplitModel->setOrderItemsData($SOrderitemsData);
                    $ordersplitModel->setOrderItemStatus($order_item_status);
                    $ordersplitModel->setAllocatedStoreids($allocatedStoreId);
                    $ordersplitModel->save();
    }
}
