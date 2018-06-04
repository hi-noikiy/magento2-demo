<?php
namespace Iksula\Checkoutcustomization\Controller\Adminhtml\Changestatus;
 
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory; 
use Magento\Sales\Model\Order;
 
class Chequereceived extends \Magento\Backend\App\Action
{ 
    const ADMIN_RESOURCE = 'Iksula_Checkoutcustomization::changestatus';

    const SALES_REP_EMAIL = 'trans_email/ident_sales/email';
 
    const STORE_REP_NAME = 'trans_email/ident_sales/name';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $request;

    protected $OrderFactory;

    protected $_storeManager;

    protected $_scopeConfig;

    protected $customerRepositoryInterface;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\OrderFactory $OrderFactory,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Iksula\EmailTemplate\Helper\Email $email
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->OrderFactory = $OrderFactory;
        $this->request = $request;
        $this->_customers = $customer;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->email = $email;
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


    public function execute()
    {   

        $params = $this->request->getParams();
        $order_id = $this->request->getParam('order_id');
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if($params){        
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();       
            $question = $objectManager->create('Iksula\Checkoutcustomization\Model\Chequedetails');
            $question->setData($params); 

            $question->save();
            if($order_id){
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                //$order_address = $objectManager->create('Magento\Sales\Model\Order\Address')->load($order_id);

                $order =  $this->OrderFactory->create()->load($order_id);

                /************************************/
                $shippingAddressObj = $order->getShippingAddress();
                $shippingAddressArray = $shippingAddressObj->getData();
                $number_ne = $shippingAddressArray['telephone'];

                /***************************************/

                $increment_id = $order->getIncrementId();
                // $orderState = Order::STATE_PENDING;
                $order->setStatus('cheque_received');
                $order->save();
                $customerId = $order->getCustomerId();
                $customer = $this->getCustomer($customerId);
                $customer_email = $customer->getEmail();
                $customer_name = $customer->getName(); 
                $currency_code = $order->getOrderCurrencyCode();
                //$number_ne = $order_address->getTelephone();
                $number_new = (int)str_replace("-","",$number_ne);                 
            }
            $this->messageManager->addSuccess( __('Check details are successfully saved.') );

            /**************************SMS****************************/

                $customer_email_new = $order->getCustomerEmail();
                $customer_name_new = $order->getCustomerFirstname()." ".$order->getCustomerLastname();

                if($customer_name_new == '')
                    $customer_name_new = $customer_name;

                if($customerId > 0){
                    //$customer =$this->customerRepositoryInterface->getById($customerId);
                    //$customerCustomAttributes = $customer->getCustomAttributes();
                    //$isAccount_telephone = $customerCustomAttributes['account_telephone'];
                    //if($isAccount_telephone->getAttributecode() == "account_telephone"){
                        
                        //$number = (int)str_replace("-","",$isAccount_telephone->getValue());
                        
                        $is_enable =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/enable_cash_at_store_receipt');
                        $template_path =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/cheque_at_store_receipt');
                        $data = array(
                            'name' => $customer_name_new,
                            'cheque_no' => $params['cheque_no'],
                            'order_id' => $increment_id
                            );

                        
                        if($is_enable && $number_new != '')
                            $objectManager->get('Iksula\EmailTemplate\Helper\Email')->smsTemplate($template_path, $data, $number_new);

                        $is_enable_acc =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/enable_cheque_realisation_accounts');
                        $template_path_acc =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/cheque_realisation_accounts');
                        $number_new_acc =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/cheque_realisation_accounts_no');
                        if($is_enable_acc && $number_new_acc != '')
                            $objectManager->get('Iksula\EmailTemplate\Helper\Email')->smsTemplate($template_path_acc, $data, $number_new_acc);
                        
                   // }
                }

                /**************************SMS****************************/

            $result['email'] =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/domain_email_id');
            $result['name'] = $this->_scopeConfig->getValue('sms_configuration/sms_setting/domain_name');

            $receiver['email'] = $this->_scopeConfig->getValue('sms_configuration/sms_setting/domain_email_id');
            $receiver['name'] = $this->_scopeConfig->getValue('sms_configuration/sms_setting/domain_name');


            $templateVars = array(
                            'store' => $this->_storeManager->getStore(),
                            'subject' => 'Your cheque has been received - Order Id: '.$increment_id.'',
                            'order_id' => $order_id,
                           'increment_id' => $increment_id,
                            'customer_name' => $customer_name,
                            'bank_name' => $params['bank_name'],
                            'cheque_no' => $params['cheque_no'],
                            'cheque_amount' => $params['cheque_amount'],
                            'date_of_cheque' => date('d-m-Y',strtotime($params['date_of_cheque'])),
                            'currency_code' => $currency_code
                        );
            
            $this->email->emailTemplate('cheque_received_accounts',$templateVars ,$result , $receiver, '','');

            $this->sendChequeReceivedNotification($customer_email_new, $customer_name_new, $order_id, $params['bank_name'], $params['cheque_no'], $params['cheque_amount'], $params['date_of_cheque'], $currency_code, $increment_id);
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    public function sendChequeReceivedNotification($customer_email, $customer_name, $order_id, $bank_name, $cheque_no, $cheque_amount, $date_of_cheque, $currency_code, $increment_id){
        $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->_storeManager->getStore()->getId());

        $templateVars = array(
                            'store' => $this->_storeManager->getStore(),
                            'customer_name' => $customer_name,
                            'subject' => 'Your cheque has been received - Order Id: '.$increment_id.'',
                            'order_id' => $order_id,
                            'increment_id' => $increment_id,
                            'bank_name' => $bank_name,
                            'cheque_no' => $cheque_no,
                            'cheque_amount' => $cheque_amount,
                            'date_of_cheque' => date('d-m-Y ',strtotime($date_of_cheque)),
                            'currency_code' => $currency_code
                        );
        $senderEmail =   $this->getSalesRepresentativeEmail();
        $senderName = $this->getSalesRepresentativeName();

        $from = array('email' => $senderEmail, 'name' => $senderName);
        $to = $customer_email;
        $transport = $this->_transportBuilder->setTemplateIdentifier('cheque_received')
                        ->setTemplateOptions($templateOptions)
                        ->setTemplateVars($templateVars)
                        ->setFrom($from)
                        ->addTo($to)
                        ->getTransport();
        $transport->sendMessage();
    }
}