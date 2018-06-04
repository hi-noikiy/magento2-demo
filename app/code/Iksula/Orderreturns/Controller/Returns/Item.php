<?php

namespace Iksula\Orderreturns\Controller\Returns;

class Item extends \Magento\Framework\App\Action\Action
{

	const SALES_REP_EMAIL = 'trans_email/ident_sales/email';
    
    const STORE_REP_NAME = 'trans_email/ident_sales/name';

	protected $url;
    protected $http;
	protected $request;
    protected $_emailHelper;
	protected $_pageFactory;
	protected $orderreturn;
	protected $_scopeConfig;
	protected $_customerSession;
	protected $_responseFactory;
	protected $customerRepositoryInterface;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\Http $http,
		\Magento\Framework\App\Request\Http $request,
		\Iksula\EmailTemplate\Helper\Email $emialHepler,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\App\ResponseFactory $responseFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Iksula\Orderreturns\Model\OrderreturnFactory $orderreturn,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
		\Magento\Customer\Model\Customer $customer)
	{
		$this->url = $url;
        $this->http = $http;
		$this->_pageFactory = $pageFactory;
		$this->request = $request;
		$this->_emailHelper = $emialHepler;
		$this->_scopeConfig = $scopeConfig;
		$this->orderreturn = $orderreturn;
		$this->_customerSession = $customerSession;
		$this->_responseFactory = $responseFactory;
		$this->customerRepositoryInterface = $customerRepositoryInterface;
		$this->_customers = $customer;
		return parent::__construct($context);
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
		if ($this->_customerSession->isLoggedIn()) {
			$returnParams = $this->request->getParams();
			$customer_id = $this->_customerSession->getCustomer()->getId();		
			$customer_email = $this->_customerSession->getCustomer()->getEmail();		
			$customer_name = $this->_customerSession->getCustomer()->getName();

			$now = date('Y-m-d');
			$todays_date = date('F d, Y', strtotime($now));
			$receiverInfo = [
							'name' => $customer_name,
							'email' => $customer_email
						];				 
			
			$receiverInfoAdmin = [
							'name' => $this->getSalesRepresentativeName(),
							'email' => $this->getSalesRepresentativeEmail(),
						];				 
						 
						/* Sender Detail  */
			$senderInfo = [
				'name' => $this->getSalesRepresentativeName(),
				'email' => $this->getSalesRepresentativeEmail(),
			];

			$emailTemplateVariables = [
				'order_id' => $returnParams['order_id'],
				'name' => $customer_name,
				'items_count' => $returnParams['return_qty'],
				'return_date' => $todays_date,
				'product_id' => $returnParams['product_id'],
			];

			$templateId = 'return_request_customer';
			$templateIdAdmin = 'return_request_admin';

			$arrayToSave = array(
								'order_id'=>$returnParams['order_id'],
								'quantity'=>round($returnParams['return_qty'],2),
								'customer_id'=>$customer_id,
								'return_reason'=>$returnParams['return_reason'],	
								'product_sku'=>$returnParams['product_sku'],	
								'product_id'=>$returnParams['product_id'],
								'product_price'=>$returnParams['product_price'],
								'return_price'=>$returnParams['return_price'],
								'return_status'=> 0										
				);
			try {
				$this->orderreturn->create()->setData($arrayToSave)->save();
				$this->_emailHelper->emailTemplate($templateId,$emailTemplateVariables,$senderInfo,$receiverInfo,'','');
				$this->_emailHelper->emailTemplate($templateIdAdmin,$emailTemplateVariables,$senderInfo,$receiverInfoAdmin,'','');

				/**************************SMS****************************/

                if($customer_id > 0){
                	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                	$order_address = $objectManager->create('Magento\Sales\Model\Order\Address')->load($returnParams['order_id']);
                	 $order_data_no = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($returnParams['order_id']);

                	//$number_ne = $order_address->getTelephone();
			        $shippingAddressObj = $order_data_no->getShippingAddress();
			        $shippingAddressArray = $shippingAddressObj->getData();
			        $number_ne = $shippingAddressArray['telephone'];
			        //$number = (int)str_replace("-","",$number_ne);
                	$number_new = (int)str_replace("-","",$number_ne);
                    
                    $customer =$this->customerRepositoryInterface->getById($customer_id);
                    $customerCustomAttributes = $customer->getCustomAttributes();
                    //$isAccount_telephone = $customerCustomAttributes['account_telephone'];
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    
                    if($number_new != ""){
                        
                       // $number = (int)str_replace("-","",$isAccount_telephone->getValue());
                        
                        $is_enable =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/enable_return_request_customer');
                        $template_path =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/return_request_customer');
                        $data = array(
                            'name' => $customer_name,
                            'order_id' => $returnParams['order_id'],                            
                            'product_id'=> $returnParams['product_id']
                            );
                        
                        if($is_enable && $number_new != '')
                            $objectManager->get('Iksula\EmailTemplate\Helper\Email')->smsTemplate($template_path, $data, $number_new);

                        
                        $is_enable_admin =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/enable_return_request_admin');
                        $template_path_admin =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/return_request_admin');
                        $number_admin =  $this->_scopeConfig->getValue('sms_configuration/sms_setting/admin_number');
                        
                        $data_admin = array(
                            'name' => $customer_name,
                            'order_id' => $returnParams['order_id'],
                            'product_id'=> $returnParams['product_id']
                            );
                        
                        if($is_enable_admin && $number_admin != '')
                            $objectManager->get('Iksula\EmailTemplate\Helper\Email')->smsTemplate($template_path_admin, $data_admin, $number_admin);
                    }
                }

                /**************************SMS****************************/

				$this->http->setRedirect($this->url->getUrl('orderreturns/returns/returnlist'), 301); 				
				
			} catch (Exception $e) {
				$this->messageManager->addError($e->getMessage());
			}

			// $RedirectUrl= $this->_url->getUrl('orderreturns/returns/returnlist');
			// $this->_responseFactory->create()->setRedirect($RedirectUrl)->sendResponse();
		}else{
			$this->http->setRedirect($this->url->getUrl('customer/account/login'), 301); 				
		}		
	}
	
}