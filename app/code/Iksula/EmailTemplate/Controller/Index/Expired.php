<?php

namespace Iksula\EmailTemplate\Controller\Index;
use Magento\Framework\App\Action\Context;
use Mirasvit\Credit\Model\BalanceFactory;
use Mirasvit\Credit\Model\TransactionFactory;

class Expired extends \Magento\Framework\App\Action\Action
{
    protected $inlineTranslation;
	protected $_mymodulemodelFactory;
	protected $scopeConfig;
	protected $date;
	protected $_transactionFactory;
	protected $customerRepositoryInterface;
	
	public function __construct(
        Context $context,
		BalanceFactory $mymodulemodelFactory,
		TransactionFactory $transactionFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {        
		$this->_mymodulemodelFactory = $mymodulemodelFactory;
		$this->_transactionFactory = $transactionFactory;
		$this->scopeConfig = $scopeConfig;
		$this->date = $date;
		$this->customerRepositoryInterface = $customerRepositoryInterface;
		parent::__construct($context);
    }

    public function execute()
    {
		$current_date = $this->date->gmtDate('Y-m-d');

		$resultPage = $this->_mymodulemodelFactory->create();
        $collection = $resultPage->getCollection(); //Get Collection of module data
		$collection_data = $collection->getData();		
		$count_collection = count($collection_data);

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$currencysymbol = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
		$currency = $currencysymbol->getStore()->getCurrentCurrencyCode();

		for($i=0; $i<$count_collection; $i++)
		{
			$updated_at = date('Y-m-d', strtotime($collection_data[$i]['updated_at']));
			$expiry_date = date("Y-m-d",strtotime("+12 month",strtotime(date("Y-m-d",strtotime($updated_at)))));
			$expired_date = date('Y-m-d', strtotime($expiry_date . ' +1 day'));

			if($expired_date == $current_date):
				if($collection_data[$i]['customer_id'] > 0):
				/****************SMS*********************/

				$customer =$this->customerRepositoryInterface->getById($collection_data[$i]['customer_id']);
				$customerCustomAttributes = $customer->getCustomAttributes();
				if (array_key_exists("account_telephone",$customerCustomAttributes)):
				$isAccount_telephone = $customerCustomAttributes['account_telephone'];
		        if($isAccount_telephone->getAttributecode() == "account_telephone"){
		            $number = (int)str_replace("-","",$isAccount_telephone->getValue());

		            $is_enable =  $this->scopeConfig->getValue('customer/sms_templates/enable');
			        $template_path =  $this->scopeConfig->getValue('store_credit/email_settings/credit_expired');
			        $data = array(
			            'name' => $collection_data[$i]['name'],
			            'store_credit_balance' => $currency." ".round($collection_data[$i]['amount'],2)
			            );
			        
			        if($is_enable && $number != '')
			         	$objectManager->get('Iksula\EmailTemplate\Helper\Email')->smsTemplate($template_path, $data, $number);
		        }
		        endif;
				/****************SMS*********************/

				if($collection_data[$i]['amount'] > 0):
					$id = $collection_data[$i]['balance_id'];
					
					$amount = $collection_data[$i]['amount'];
					$data = array();
					$resultRedirect = $this->_transactionFactory->create();
					$data['balance_id'] = 	$id;
					$data['created_at'] = date('Y-m-d H:i:s');
					$data['updated_at'] = date('Y-m-d H:i:s');
					$data['balance_delta'] = -$amount;
					$data['balance_amount'] = 0;
					$data['is_notified'] = 0;
					$data['message'] = 'expired';
					$data['action'] = 'expired';
					$resultRedirect->setData($data);
					$resultRedirect->save();

		            if ($id) {
		                $resultPage->load($id);
						$resultPage->setAmount(0)->save();
		            }
									

					// Receiver Detail
					$receiverInfo = [
						'name' => $collection_data[$i]['name'],
						'email' => $collection_data[$i]['email']
					];				 
					 
					$domain_name =  $this->scopeConfig->getValue('sms_configuration/sms_setting/domain_name');
    				$domain_email_id =  $this->scopeConfig->getValue('sms_configuration/sms_setting/domain_email_id');		 
					 
					/* Sender Detail  */
					$senderInfo = [
						'name' => $domain_name,
						'email' => $domain_email_id,
					];
					 
					$templateId = 'expired';
					 
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$currencysymbol = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
					$currency = $currencysymbol->getStore()->getCurrentCurrencyCode();

					$emailTempVariables = array();
					$emailTempVariables['amount'] = $currency." ".round($collection_data[$i]['amount'],2);
					$emailTempVariables['name'] = $collection_data[$i]['name'];
						
					$objectManager->get('Iksula\EmailTemplate\Helper\Email')->emailTemplate($templateId,$emailTempVariables,$senderInfo,$receiverInfo,'','');
				endif;
				endif;
			endif;
		}		
    }
}