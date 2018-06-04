<?php
namespace Iksula\Checkoutcustomization\Controller\Adminhtml\Changestatus;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;

class Cashreceived extends Action
{
    const ADMIN_RESOURCE = 'Iksula_Checkoutcustomization::changestatus';

    const SALES_REP_EMAIL = 'trans_email/ident_sales/email';

    const STORE_REP_NAME = 'trans_email/ident_sales/name';

    const CASH_RECEIVED_NOTIFICATION = 'Cash at store received';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $request;

    protected $_storeManager;

    protected $_transportBuilder;

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
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->_storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_customers = $customer;
        $this->_scopeConfig = $scopeConfig;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
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
        $this->request->getParams();
        $order_id = $this->request->getParam('order_id');

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if($order_id){
            try {

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $order = $objectManager->create('\Magento\Sales\Model\Order')->load($order_id);

                /************************************/
                $shippingAddressObj = $order->getShippingAddress();
                $shippingAddressArray = $shippingAddressObj->getData();
                $number_ne = $shippingAddressArray['telephone'];

                /***************************************/

                $order_address = $objectManager->create('Magento\Sales\Model\Order\Address')->load($order_id);
                $orderState = Order::STATE_PROCESSING;
                $order->setState($orderState)->setStatus('cash_received');
                $order->save();


                /********** Ordersplit when success controller is called ***********/

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $ordersplitlogicHelper = $objectManager->get('\Iksula\Ordersplit\Helper\Ordersplitlogic');
                $ordersplitlogicHelper->OrdersplitOfOrders($order_id);
                /******************************************************************/


                $increment_id = $order->getIncrementId();
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
                   // $customerCustomAttributes = $customer->getCustomAttributes();
                   // $isAccount_telephone = $customerCustomAttributes['account_telephone'];
                   // if($isAccount_telephone->getAttributecode() == "account_telephone"){

                        //$number = (int)str_replace("-","",$isAccount_telephone->getValue());

                        $is_enable =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/enable_cash_at_store_receipt');
                        $template_path =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/cash_at_store_receipt');
                        $data = array(
                            'name' => $customer_name_new,
                            'order_id' => $increment_id
                            );

                        if($is_enable && $number_new != '')
                            $objectManager->get('Iksula\EmailTemplate\Helper\Email')->smsTemplate($template_path, $data, $number_new);
                    //}
                }

                /**************************SMS****************************/

                $this->sendCashReceivedNotification($customer_email_new, $customer_name_new, $increment_id);

            } catch (Exception $e) {

            }
            $order->getCustomerEmail();
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

       return $resultPage;
    }

    public function sendCashReceivedNotification($customer_email, $customer_name, $order_id){
        $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->_storeManager->getStore()->getId());
        $templateVars = array(
                            'store' => $this->_storeManager->getStore(),
                            'customer_name' => $customer_name,
                            'subject' => self::CASH_RECEIVED_NOTIFICATION,
                            'order_id' => $order_id
                        );
        $senderEmail =   $this->getSalesRepresentativeEmail();
        $senderName = $this->getSalesRepresentativeName();
        $from = array('email' => $senderEmail, 'name' => $senderName);
        $to = $customer_email;
        $transport = $this->_transportBuilder->setTemplateIdentifier('cash_rereived')
                        ->setTemplateOptions($templateOptions)
                        ->setTemplateVars($templateVars)
                        ->setFrom($from)
                        ->addTo($to)
                        ->getTransport();
        $transport->sendMessage();
    }
}
