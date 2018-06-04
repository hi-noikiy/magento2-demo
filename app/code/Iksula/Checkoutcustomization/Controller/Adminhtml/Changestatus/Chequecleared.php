<?php
namespace Iksula\Checkoutcustomization\Controller\Adminhtml\Changestatus;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;

class Chequecleared extends Action
{
    const ADMIN_RESOURCE = 'Iksula_Checkoutcustomization::changestatus';

    const SALES_REP_EMAIL = 'trans_email/ident_sales/email';

    const STORE_REP_NAME = 'trans_email/ident_sales/name';

    const CHEQUE_CLEARED_NOTIFICATION = 'Your cheque is cleared';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $request;

    protected $_storeManager;

    protected $_transportBuilder;

    protected $_scopeConfig;

    protected $_chequeCollection;

    protected $customerRepositoryInterface;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Iksula\Checkoutcustomization\Model\ResourceModel\Chequedetails\CollectionFactory $_chequeCollection,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        array $data = []
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->_storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_customers = $customer;
        $this->_scopeConfig = $scopeConfig;
        $this->_chequeCollection = $_chequeCollection;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        parent::__construct($context,$data);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */

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
        $data = $this->request->getParams();
        $order_id = $this->request->getParam('order_id');

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if($order_id){
            try {

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                //$order_address = $objectManager->create('Magento\Sales\Model\Order\Address')->load($order_id);
                /**********************************/
                $orderobj = $objectManager->create('\Magento\Sales\Model\Order')->load($order_id);
                $shippingAddressObj = $orderobj->getShippingAddress();
                $shippingAddressArray = $shippingAddressObj->getData();
                $number_ne = $shippingAddressArray['telephone'];
                /************************************/

                $cheque_info = $this->_chequeCollection->create()->addFieldToFilter('order_id',$order_id)->load();
                $cheque_details = $cheque_info->getData();

                $bank_name = $cheque_details[0]['bank_name'];
                $cheque_no = $cheque_details[0]['cheque_no'];
                $cheque_amount = $cheque_details[0]['cheque_amount'];
                $date_of_cheque = date('m-d-Y', strtotime($cheque_details[0]['date_of_cheque']));

                $order = $objectManager->create('\Magento\Sales\Model\Order')->load($order_id);
                $increment_id = $order->getIncrementId();
                $currency_code = $order->getOrderCurrencyCode();
                $orderState = Order::STATE_PROCESSING;
                $order->setState($orderState)->setStatus('cheque_cleared');
                $order->save();



                /********** Ordersplit when success controller is called ***********/

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $ordersplitlogicHelper = $objectManager->get('\Iksula\Ordersplit\Helper\Ordersplitlogic');
                $ordersplitlogicHelper->OrdersplitOfOrders($order_id);
                /******************************************************************/

                $customerId = $order->getCustomerId();

                $customer = $this->getCustomer($customerId);
                $customer_email = $customer->getEmail();
                $customer_name = $customer->getName();
                //$number_ne = $order_address->getTelephone();
                $number_new = (int)str_replace("-","",$number_ne);
                $customer_email_new = $order->getCustomerEmail();
                $customer_name_new = $order->getCustomerFirstname()." ".$order->getCustomerLastname();

                if($customer_name_new == '')
                    $customer_name_new = $customer_name;

                /**************************SMS****************************/

                if($customerId > 0){
                    //$customer =$this->customerRepositoryInterface->getById($customerId);
                    //$customerCustomAttributes = $customer->getCustomAttributes();
                    //$isAccount_telephone = $customerCustomAttributes['account_telephone'];
                    //if($isAccount_telephone->getAttributecode() == "account_telephone"){

                        //$number = (int)str_replace("-","",$isAccount_telephone->getValue());

                        $is_enable =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/enable_cheque_cleared');
                        $template_path =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/cheque_cleared');
                        $data = array(
                            'name' => $customer_name,
                            'order_id' => $increment_id
                            );


                        if($is_enable && $number_new != '')
                            $objectManager->get('Iksula\EmailTemplate\Helper\Email')->smsTemplate($template_path, $data, $number_new);
                    //}
                }

                /**************************SMS****************************/

                $this->sendChequeClearedNotification($customer_email_new, $customer_name_new, $increment_id , $bank_name, $cheque_no, $cheque_amount, $date_of_cheque, $currency_code);
            } catch (Exception $e) {

            }
            $order->getCustomerEmail();
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }



       return $resultPage;
    }

    public function sendChequeClearedNotification($customer_email, $customer_name, $order_id, $bank_name, $cheque_no, $cheque_amount, $date_of_cheque, $currency_code){
        $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->_storeManager->getStore()->getId());
        $templateVars = array(
                            'store' => $this->_storeManager->getStore(),
                            'customer_name' => $customer_name,
                            'subject' => self::CHEQUE_CLEARED_NOTIFICATION,
                            'order_id' => $order_id,
                            'bank_name' => $bank_name,
                            'cheque_no' => $cheque_no,
                            'cheque_amount' => $cheque_amount,
                            'date_of_cheque' => $date_of_cheque,
                            'currency_code' => $currency_code,
                            'message'   => 'Your Cheque is cleared with the respective bank!!.'
                        );
        $senderEmail =   $this->getSalesRepresentativeEmail();
        $senderName = $this->getSalesRepresentativeName();

        $from = array('email' => $senderEmail, 'name' => $senderName);
        $to = $customer_email;
        $transport = $this->_transportBuilder->setTemplateIdentifier('cheque_cleared')
                        ->setTemplateOptions($templateOptions)
                        ->setTemplateVars($templateVars)
                        ->setFrom($from)
                        ->addTo($to)
                        ->getTransport();
        $transport->sendMessage();
    }
}
