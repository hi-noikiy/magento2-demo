<?php

namespace Iksula\LoginWithMobile\Helper;

use \Magento\Framework\App\Helper\AbstractHelper as CoreHelper;

class Data extends CoreHelper
{

	protected $customerSession;
	protected $_customers;

	public function __construct(
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Customer\Model\Customer $customers
		){
		$this->customerSession = $customerSession;
		$this->_customers = $customers;
	}
	public function getcanRegister($phone = ""){
		$collection = $this->getCustomerCollection();
		$collection->joinAttribute('account_telephone', 'customer/account_telephone', 'entity_id', null, 'left');
		$collection->addAttributeToFilter(
			array(
				//array('attribute' => 'email', 'like' => $email),
				array('attribute' => 'account_telephone', 'like' => $phone),
				)
			);
		
		return $collection->getSize() === 0;

	}

	public function getCustomerCollection(){
        return $this->_customers->getCollection();
    } 
}