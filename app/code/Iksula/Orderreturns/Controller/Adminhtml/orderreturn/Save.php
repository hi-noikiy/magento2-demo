<?php
namespace Iksula\Orderreturns\Controller\Adminhtml\orderreturn;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;


class Save extends \Magento\Backend\App\Action
{

    /**
     * @param Action\Context $context
     */

    const SALES_REP_EMAIL = 'trans_email/ident_sales/email';
    
    const STORE_REP_NAME = 'trans_email/ident_sales/name';

    protected $balanceFactory;

    protected $_scopeConfig;

    protected $_emailHelper;

    protected $_customerData;

    protected $customerRepositoryInterface;

    public function __construct(Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Iksula\EmailTemplate\Helper\Email $emialHepler,
        \Magento\Customer\Model\CustomerFactory $customerdata,
        \Mirasvit\Credit\Model\BalanceFactory $earningFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\Customer $customer
        )
    {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_emailHelper = $emialHepler;
        $this->_customerData = $customerdata;
        $this->balanceFactory = $earningFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->_customers = $customer;
    }

    public function getCollection()
    {
        //Get customer collection
        return $this->_customers->getCollection();
    }

    public function getCustomer($customerId)
    {
        //Get customer by customerID
        return $this->_customers->load($customerId);
    }

    public function getSalesRepresentativeEmail() {
         $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
         return $this->_scopeConfig->getValue(self::SALES_REP_EMAIL, $storeScope); //you get your value here
    }

    public function getSalesRepresentativeName() {
         $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
         return $this->_scopeConfig->getValue(self::STORE_REP_NAME, $storeScope); //you get your value here
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if(isset($data['pickup_time'])){                
            $datatime =$data['pickup_time'];
            $time  = implode(':', $datatime);
            $data['pickup_time'] =$time;
        }
        if(isset($data['pickup_date'])){                
            $return_date = $data['pickup_date']." ".$data['pickup_time'];
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create('Iksula\Orderreturns\Model\Orderreturn');

            $id = $this->getRequest()->getParam('id');
            $currentModel = $model->load($id);
            


            $customerId = $currentModel->getCustomerId();
            $orderId = $currentModel->getOrderId();
            $product_id = $currentModel->getProductId();
            $productSku = $currentModel->getProductSku();
            $returnquantity = $currentModel->getQuantity();
            $returnitemPrice = $currentModel->getReturnPrice();
            $returnpickup_time = $currentModel->getPickupTime();
            $returnreturn_status = $currentModel->getReturnStatus();
            $returnpickup_date = $currentModel->getPickupDate();
            if(isset($returnpickup_date) && isset($returnpickup_time)){
                $returnSchedule = $returnpickup_date." ".$returnpickup_time;
            }
          
            $totalreturnPrice  = $returnquantity*$returnitemPrice;



            // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerObj =  $this->_customerData->create()
            ->load($customerId);
            $customerEmail = $customerObj->getEmail();
            $customerName = $customerObj->getName();

            $receiverInfo = [
                    'name' => $customerName,
                    'email' => $customerEmail
                        ];               
                            
            $senderInfo = [
                'name' => $this->getSalesRepresentativeName(),
                'email' => $this->getSalesRepresentativeEmail(),
            ];

            
            if ($id) {
                $model->load($id);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            }
            try{
                $uploader = $this->_objectManager->create(
                    'Magento\MediaStorage\Model\File\Uploader',
                    ['fileId' => 'image']
                );
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
                $imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
                $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                    ->getDirectoryRead(DirectoryList::MEDIA);
                $result = $uploader->save($mediaDirectory->getAbsolutePath('emizen_banner'));
                    if($result['error']==0)
                    {
                        $data['image'] = 'emizen_banner' . $result['file'];
                    }
            } catch (\Exception $e) {
                //unset($data['image']);
            }
            //var_dump($data);die;
            if(isset($data['image']['delete']) && $data['image']['delete'] == '1')
                $data['image'] = '';
            // echo "aaa";
           
            if(isset($data['return_status']) && $data['return_status']==1 && $returnreturn_status==0){
                $emailTemplateVariables = [
                    'order_id' => $orderId,
                    'name' => $customerName,
                    'return_date' => $return_date,
                    'product_id' => $product_id,
                    'items_count' => 1
                ];

                $pickuptemplateId = 'return_pickup_schedule';

                $this->_emailHelper->emailTemplate($pickuptemplateId,$emailTemplateVariables,$senderInfo,$receiverInfo,'','');

                /**************************SMS****************************/

                if($customerId > 0){
                    $customer =$this->customerRepositoryInterface->getById($customerId);
                    $customerCustomAttributes = $customer->getCustomAttributes();
                    if(array_key_exists('account_telephone', $customerCustomAttributes)):
                        $isAccount_telephone = $customerCustomAttributes['account_telephone'];
                        if($isAccount_telephone->getAttributecode() == "account_telephone"){
                            
                            $number = (int)str_replace("-","",$isAccount_telephone->getValue()); 
                            
                            $is_enable =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/enable_pickup_schedule');
                            $template_path =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/pickup_schedule');
                            $smsdata = array(
                                'name' => $customerName,
                                'order_id' => $orderId,
                                'pickup_time' => $return_date                            
                                );
                            
                            if($is_enable && $number != '')
                                $this->_objectManager->get('Iksula\EmailTemplate\Helper\Email')->smsTemplate($template_path, $smsdata, $number);
                        }
                    endif;
                }

                /**************************SMS****************************/
            }
            if(isset($data['return_status']) && $data['return_status']==2 && $returnreturn_status==1){
                $emailTemplateVariables = [
                    'order_id' => $orderId,
                    'name' => $customerName,
                    'product_id' => $product_id,
                    'items_count' => 1
                ];

                $receivedtemplateId = 'return_received';

                $this->_emailHelper->emailTemplate($receivedtemplateId,$emailTemplateVariables,$senderInfo,$receiverInfo,'','');

                /**************************SMS****************************/

                if($customerId > 0){
                    $customer =$this->customerRepositoryInterface->getById($customerId);
                    $customerCustomAttributes = $customer->getCustomAttributes();
                    if(array_key_exists('account_telephone', $customerCustomAttributes)):
                        $isAccount_telephone = $customerCustomAttributes['account_telephone'];
                        if($isAccount_telephone->getAttributecode() == "account_telephone"){
                            
                            $number = (int)str_replace("-","",$isAccount_telephone->getValue());
                            
                            $is_enable =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/enable_return_received');
                            $template_path =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/return_received');
                            $smsdata = array(
                                'name' => $customerName,
                                'order_id' => $orderId,
                                'pickup_time' => $returnSchedule                            
                                );
                            
                            if($is_enable && $number != '')
                                $this->_objectManager->get('Iksula\EmailTemplate\Helper\Email')->smsTemplate($template_path, $smsdata, $number);
                        }
                    endif;
                }

                /**************************SMS****************************/
            }
            if(isset($data['return_status']) && $data['return_status']==3 && $returnreturn_status==2){                    
                $balance = $this->balanceFactory->create()->loadByCustomer($customerId);
                $balance->addTransaction(
                                $totalreturnPrice,
                                \Mirasvit\Credit\Model\Transaction::ACTION_MANUAL,
                                "Refund amount against<br>Order Id: ".$orderId. "<br>Item Sku: ". $productSku
                            );

                

                $emailTemplateVariables = [
                    'order_id' => $orderId,
                    'name' => $customerName,
                    'product_id' => $product_id,
                    'items_count' => 1
                ];

                $credittemplateId = 'refund_via_store_credits';

                $this->_emailHelper->emailTemplate($credittemplateId,$emailTemplateVariables,$senderInfo,$receiverInfo,'','');

                /**************************SMS****************************/

                if($customerId > 0){
                    $customer =$this->customerRepositoryInterface->getById($customerId);
                    $customerCustomAttributes = $customer->getCustomAttributes();
                    if(array_key_exists('account_telephone', $customerCustomAttributes)):
                        $isAccount_telephone = $customerCustomAttributes['account_telephone'];
                        if($isAccount_telephone->getAttributecode() == "account_telephone"){
                            
                            $number = (int)str_replace("-","",$isAccount_telephone->getValue());
                            
                            $is_enable =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/enable_refund_via_store_credit');
                            $template_path =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/refund_via_store_credit');
                            $smsdata = array(
                                'name' => $customerName,
                                'order_id' => $orderId,
                                'pickup_time' => $returnSchedule                            
                                );
                            
                            if($is_enable && $number != '')
                                $this->_objectManager->get('Iksula\EmailTemplate\Helper\Email')->smsTemplate($template_path, $smsdata, $number);
                        }
                    endif;
                }

                /**************************SMS****************************/

            }
            if(isset($data['return_status']) && $data['return_status']==4 && $returnreturn_status==2){
                $emailTemplateVariables = [
                    'order_id' => $orderId,
                    'name' => $customerName,
                    'product_id' => $product_id,
                    'items_count' => 1
                ];

                $receivedtemplateId = 'cash_refund';

                $this->_emailHelper->emailTemplate($receivedtemplateId,$emailTemplateVariables,$senderInfo,$receiverInfo,'','');

                /**************************SMS****************************/

                if(isset($customerId)){
                    $customer =$this->customerRepositoryInterface->getById($customerId);
                    $customerCustomAttributes = $customer->getCustomAttributes();
                    if(array_key_exists('account_telephone', $customerCustomAttributes)):
                        $isAccount_telephone = $customerCustomAttributes['account_telephone'];
                        if($isAccount_telephone->getAttributecode() == "account_telephone"){
                            
                            $number = (int)str_replace("-","",$isAccount_telephone->getValue());
                            
                            $is_enable =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/enable_cash_refund');
                            $template_path =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/cash_refund');
                            $smsdata = array(
                                'name' => $customerName,
                                'order_id' => $orderId,
                                'pickup_time' => $returnSchedule                            
                                );
                            
                            if($is_enable && $number != '')
                                $this->_objectManager->get('Iksula\EmailTemplate\Helper\Email')->smsTemplate($template_path, $smsdata, $number);
                        }
                    endif;
                }

                /**************************SMS****************************/
            }

                
                $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Orderreturn has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Orderreturn.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}