<?php

namespace Iksula\EmailTemplate\Controller\Index;
use Magento\Framework\App\Action\Context;
use Mirasvit\Credit\Model\BalanceFactory;

class FinalReminder extends \Magento\Framework\App\Action\Action
{
    protected $inlineTranslation;
	protected $_mymodulemodelFactory;
	protected $scopeConfig;
	protected $date;
	protected $customerRepositoryInterface;
	
	public function __construct(
        Context $context,
		BalanceFactory $mymodulemodelFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface

    ) {        
		$this->_mymodulemodelFactory = $mymodulemodelFactory;
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

		/*echo "<pre>";
		print_r($collection_data);*/
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$currencysymbol = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
		$currency = $currencysymbol->getStore()->getCurrentCurrencyCode();
        
		for($i=0; $i<$count_collection; $i++)
		{
			/* Assign values for your template variables  */
			$emailTempVariables = array();
			$emailTempVariables['amount'] = $currency." ".round($collection_data[$i]['amount'],2);
			$emailTempVariables['name'] = $collection_data[$i]['name'];
			$updated_at = date('Y-m-d', strtotime($collection_data[$i]['updated_at']));
			$expiry_date = date("Y-m-d",strtotime("+11 month",strtotime(date("Y-m-d",strtotime($updated_at)))));
			$emailTempVariables['real_expiry_date'] = date("m-d-Y",strtotime("+1 month",strtotime(date("Y-m-d",strtotime($expiry_date)))));

			if($expiry_date == $current_date):
			if($collection_data[$i]['customer_id'] > 0):
				$customer =$this->customerRepositoryInterface->getById($collection_data[$i]['customer_id']);
				$customerCustomAttributes = $customer->getCustomAttributes();
				if(array_key_exists("account_telephone",$customerCustomAttributes)):
				$isAccount_telephone = $customerCustomAttributes['account_telephone'];
		        if($isAccount_telephone->getAttributecode() == "account_telephone"){
		            $number = (int)str_replace("-","",$isAccount_telephone->getValue());

		            $is_enable =  $this->scopeConfig->getValue('customer/sms_templates/enable');
			        $template_path =  $this->scopeConfig->getValue('store_credit/email_settings/final_reminder');
			        $data = array(
			            'name' => $collection_data[$i]['name'],
			            'store_credit_balance' => $currency." ".round($collection_data[$i]['amount'],2)
			            );

			        //$number =  "971528481421";
			        
			        if($is_enable && $number != '')
			         	$objectManager->get('Iksula\EmailTemplate\Helper\Email')->smsTemplate($template_path, $data, $number);
		        }
		        endif;
				if($collection_data[$i]['amount'] > 0):					

					/* Receiver Detail  */
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
					 
					$templateId = 'final_reminder';
					 
						
					$objectManager->get('Iksula\EmailTemplate\Helper\Email')->emailTemplate($templateId,$emailTempVariables,$senderInfo,$receiverInfo,'','');
				endif;
			endif;
			endif;
		}		
    }
}