<?php

namespace Iksula\MyAccount\Block\Index;


class Index extends \Magento\Framework\View\Element\Template {

    protected $_customerSession;
	protected $_customerRepositoryInterface;
	
	public function __construct(\Magento\Catalog\Block\Product\Context $context, \Magento\Customer\Model\Session $customerSession,  \Magento\Customer\Api\CustomerRepositoryInterfaceFactory $customerRepositoryFactory, array $data = []) {
		$this->_customerSession = $customerSession;
		$this->_customerRepositoryInterface = $customerRepositoryFactory->create();
        parent::__construct($context, $data);
	}

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
	
	public function getLoggedinCustomerName()
	{	
		$customer_id = $this->_customerSession->getData('customer_id');
        if($customer_id)
        {
            $customer = $this->_customerRepositoryInterface->getById($customer_id);			
			$full_name = $customer->getFirstName()." ".$customer->getLastName();
            return $full_name;
        }
        else
        {
            return "Guest";
        }
    }

}