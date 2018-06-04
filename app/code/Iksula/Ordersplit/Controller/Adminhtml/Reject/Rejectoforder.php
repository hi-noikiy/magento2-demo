<?php
namespace Iksula\Ordersplit\Controller\Adminhtml\Reject;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class  Rejectoforder extends Action
{


    protected $rejectionFactory;
    protected $ordersplitFactory;
    protected $emailidshelper;
    protected $storemanagerhelper;
    protected $scopeConfig;
    protected $orderFactoryData;


    public function __construct(Context $context
                                , \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
                                , \Iksula\Ordersplit\Model\RejectionFactory $rejectionFactory
                                ,\Iksula\EmailTemplate\Helper\Email $emailidshelper
                                ,\Iksula\Storemanager\Helper\Data $storemanagerhelper
                                , \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
                                , \Magento\Sales\Model\OrderFactory    $orderFactoryData
                                ) {
        $this->rejectionFactory = $rejectionFactory;
        $this->ordersplitFactory = $ordersplitFactory;
        $this->emailidshelper = $emailidshelper;
        $this->storemanagerhelper = $storemanagerhelper;
        $this->scopeConfig = $scopeConfig;
        $this->orderFactoryData = $orderFactoryData;
        parent::__construct($context);
    }

    public function execute()
    {


        $rejection_reason = $this->getRequest()->getPost('rejection_reason');
        $allocated_storeid = $this->getRequest()->getPost('allocated_storeid');
        $order_id = $this->getRequest()->getPost('order_id');
        $row_id = $this->getRequest()->getPost('row_id');
        $order_incrementid = $this->orderFactoryData->create()->load($order_id)->getIncrementId();
        $order_unique_id = $this->ordersplitFactory->create()->load($row_id)->getOrderItemId();

        $rejectionCollection = $this->rejectionFactory->create();
        $rejectionCollection->setOrderId($order_id);
        $rejectionCollection->setOrdersplitUniqueid($order_unique_id);
        $rejectionCollection->setOrdersplitId($row_id);
        $rejectionCollection->setRejectionComment($rejection_reason);
        $rejectionCollection->setRejectedStoreid($allocated_storeid);
        $rejectionCollection->save();

        $allocated_storeid = $this->ordersplitFactory->create()->load($row_id)->getAllocatedStoreids();
        $allocatedstorecode = $this->storemanagerhelper->getStoreCodeByStoreId($allocated_storeid);
        $storeobj = $this->storemanagerhelper->getStoreManagerObject($allocated_storeid);
                            $store_name = $storeobj->getStoreName();
                            $store_emailid = $storeobj->getStoreEmailid();


        $receiver['email'] = $this->scopeConfig->getValue('sms_configuration/sms_setting/domain_email_id');
        $receiver['name'] = $this->scopeConfig->getValue('sms_configuration/sms_setting/domain_name');


            $senderInfo = ['name' => $receiver['name'] , 'email' => $receiver['email']];
            $receiverInfo = ['name' => '2xl' , 'email' => $receiver['email']];
            $emailTempVariables = ['row_id' => $row_id , 'order_id' => $order_incrementid , 'store_code' => $allocatedstorecode];


            $this->emailidshelper->emailTemplate('order_rejected' , $emailTempVariables ,$senderInfo,$receiverInfo,'' , '');

            $is_enable =  $this->scopeConfig->getValue('sms_configuration/sms_setting/enable_rejection');
                            $template_path =  $this->scopeConfig->getValue('sms_configuration/sms_setting/order_rejection');
                            $ordersplitId = $this->ordersplitFactory->create()->load($row_id)->getOrderItemId();
                            $data = array(
                            'order_id' => $order_incrementid,
                            'store_name' => $store_name,
                            'store_code' => $allocatedstorecode,
                            'ordersplit_id' => $order_unique_id
                            );

                            $AdminNumber  = $this->scopeConfig->getValue('sms_configuration/sms_setting/admin_number');
                            $storeNumber = $storeobj->getStoreMobileno();
                            $aNumber = array($AdminNumber , $storeNumber);

                            if($is_enable)
                                $this->emailidshelper->smsTemplate($template_path, $data, $aNumber);



        $ordersplitCollection = $this->ordersplitFactory->create()->load($row_id);
        $ordersplitCollection->setAllocatedStoreids('');
        $ordersplitCollection->setOrderItemStatus('store_rejected');
        $ordersplitCollection->save();

        $result ['error'] = 0;
        $result ['result_content'] = "Data is updated";

        echo json_encode($result);

    }

}
