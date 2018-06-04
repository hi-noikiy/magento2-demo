<?php
namespace Iksula\Checkoutcustomization\Controller\Adminhtml\Changestatus;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;

class Completeorder extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $request;

    protected $emailidshelper;


    protected $orderfactorydata;


    protected $scopeConfig;

      protected $ordersplitFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request
        ,\Iksula\EmailTemplate\Helper\Email $emailidshelper
        ,\Magento\Sales\Model\Order $orderfactorydata
        ,\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
        , \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->emailidshelper = $emailidshelper;
        $this->orderfactorydata = $orderfactorydata;
        $this->scopeConfig = $scopeConfig;
        $this->ordersplitFactory = $ordersplitFactory;
    }


    public function execute()
    {

        $params = $this->request->getParams();
        $order_id = $this->request->getParam('order_id');
        $emirates_id = $this->request->getParam('emirates_id');

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if($params){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $question = $objectManager->create('Iksula\Checkoutcustomization\Model\Chequedetails');
            $question->setData($params);
            $question->save();
            if($order_id && $emirates_id){
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $order = $objectManager->create('\Magento\Sales\Model\Order')->load($order_id);
                
                
                 $orderState = Order::STATE_COMPLETE;
                 $orderstatus = 'complete';
                $order->setCustomerEmiratesId($emirates_id);
                $order->setState($orderState);
                $order->setStatus($orderstatus);
                $order->save();


                $orderSplitCollectionData = $this->ordersplitFactory->create()
                                         ->getCollection()
                                         ->addFieldToFilter('order_id', array('eq' => $order_id))
                                         ->getData();

                                         foreach($orderSplitCollectionData as $ordersplitData){
                                           if($this->checkIfOrderItemCancelled($ordersplitData['id'])){
                                             continue;
                                           }
                                               $ordersplitObj = $this->ordersplitFactory->create()->load($ordersplitData['id']);
                                               $ordersplitObj->setOrderItemStatus($orderstatus);
                                                 $ordersplitObj->save();
                                        }

                  $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $orderobj = $objectManager->create('\Magento\Sales\Model\Order')->load($order_id);
                $incrementid = $orderobj->getIncrementId();


                $shippingAddressObj = $orderobj->getShippingAddress();
                $shippingAddressArray = $shippingAddressObj->getData();
                  $firstname = $shippingAddressArray['firstname'];

                  $lastname = $shippingAddressArray['lastname'];
                  $telephone = $shippingAddressArray['telephone'];
                  $email = $shippingAddressArray['email'];
                  $name = $firstname . ' ' .  $lastname;

                  $customer_email_new = $orderobj->getCustomerEmail();

                  $receiver['email'] = $this->scopeConfig->getValue('sms_configuration/sms_setting/domain_email_id');
                  $receiver['name'] = $this->scopeConfig->getValue('sms_configuration/sms_setting/domain_name');

                $emailTempVariables = ['order_id' => $incrementid , 'name' => $name];


                $sender_info = ['name' => $receiver['name'], 'email' => $receiver['email']];
                $receiver_info = ['name' => $name, 'email' => $customer_email_new];

                $this->emailidshelper->emailTemplate('order_delivered' , $emailTempVariables ,$sender_info,$receiver_info,'','');
                $this->emailidshelper->emailTemplate('order_delivered_by_courier_admin' , $emailTempVariables ,$sender_info,$sender_info,'','');



                $is_enable =  $this->scopeConfig->getValue('sms_configuration/sms_setting/enable_order_delivered');
                            $template_path =  $this->scopeConfig->getValue('sms_configuration/sms_setting/order_delivered');
                          
                            $data = array(
                            'order_id' => $incrementid
                            );

                            $AdminNumber  = $this->scopeConfig->getValue('sms_configuration/sms_setting/admin_number');
                            
                            $aNumber = array($AdminNumber);
                            
                            if($is_enable)
                                $this->emailidshelper->smsTemplate($template_path, $data, $aNumber);

                              $is_enable =  $this->scopeConfig->getValue('sms_configuration/sms_setting/enable_delivered_courier');
                            $template_path =  $this->scopeConfig->getValue('sms_configuration/sms_setting/order_delivered_by_courier_guy');
                          
                            $data = array(
                            'order_id' => $incrementid
                            );

                            $number_ne = $telephone;
                            $aNumber = (int)str_replace("-","",$number_ne);
                            
                            if($is_enable)
                                $this->emailidshelper->smsTemplate($template_path, $data, $aNumber);

            }
            $this->messageManager->addSuccess( __('Emirates Id is saved and Order is complete.') );
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    public function checkIfOrderItemCancelled($row_id){

        $order_item_status = $this->ordersplitFactory->create()->load($row_id)->getOrderItemStatus();
        if($order_item_status == 'store_cancelled'){
            return true;
        }else{
          return false;
        }

    }
}
