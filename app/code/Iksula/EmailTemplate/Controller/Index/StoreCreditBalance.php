<?php

namespace Iksula\EmailTemplate\Controller\Index;
use Magento\Framework\App\Action\Context;
use Mirasvit\Credit\Model\BalanceFactory;

class StoreCreditBalance extends \Magento\Framework\App\Action\Action
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
    	/**/
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$base_url = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
		/**/
		$day = $this->scopeConfig->getValue('store_credit/email_settings/day', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);	
		$current_day = $this->date->gmtDate('d');

		$resultPage = $this->_mymodulemodelFactory->create();
        $collection = $resultPage->getCollection(); //Get Collection of module data
		$collection_data = $collection->getData();
		$count_collection = count($collection_data);

		//echo "<pre>";
		//print_r($collection_data); exit;
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$currencysymbol = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
		$currency = $currencysymbol->getStore()->getCurrentCurrencyCode();
		if($current_day == $day)
		{ echo "success";
			for($i=0; $i<$count_collection; $i++)
			{
				if($collection_data[$i]['customer_id'] > 0){
					$updated_at = date('Y-m-d', strtotime($collection_data[$i]['updated_at']));
					$expiry_date = date("Y-m-d",strtotime("+12 month",strtotime(date("Y-m-d",strtotime($updated_at)))));
					$customer =$this->customerRepositoryInterface->getById($collection_data[$i]['customer_id']);
					$customerCustomAttributes = $customer->getCustomAttributes();
					if (array_key_exists("account_telephone",$customerCustomAttributes)):
						$isAccount_telephone = $customerCustomAttributes['account_telephone'];
				        if($isAccount_telephone->getAttributecode() == "account_telephone"){
				        	$number = (int)str_replace("-","",$isAccount_telephone->getValue());
				        	
				            $is_enable =  $this->scopeConfig->getValue('customer/sms_templates/enable');
					        $template_path =  $this->scopeConfig->getValue('store_credit/email_settings/credit_balance');
					        $data = array(
					            'name' => $collection_data[$i]['name'],
					            'store_credit_balance' => $currency." ".round($collection_data[$i]['amount'],2)
					            );
					        
					        if($is_enable && $number != '')
					         	$objectManager->get('Iksula\EmailTemplate\Helper\Email')->smsTemplate($template_path, $data, $number);
				        }
				    endif;
					/* Assign values for your template variables  */
					$emailTempVariables = array();
					$emailTempVariables['amount'] = $currency." ".round($collection_data[$i]['amount'],2);
					$emailTempVariables['name'] = $collection_data[$i]['name'];
					$emailTempVariables['expiry_date'] = date("m-d-Y",strtotime($expiry_date));

					if($collection_data[$i]['amount'] > 0 && $collection_data[$i]['email'] != ''):					

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
						 
						$templateId = 'store_credit_balance';	

										 
							
						$objectManager->get('Iksula\EmailTemplate\Helper\Email')->emailTemplate($templateId,$emailTempVariables,$senderInfo,$receiverInfo,'','');

					endif;
				}
			}
		}
		else
		{
			echo "failed";
		}
		
    }
}