<?php

namespace Iksula\Orderreturns\Controller\Returns;

class Returnlist extends \Magento\Framework\App\Action\Action
{

	protected $url;

    protected $http;

    protected $customerSession;

	protected $_pageFactory;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\Http $http,
        \Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->url = $url;
        $this->http = $http;
        $this->customerSession = $customerSession;
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{		
		if ($this->customerSession->isLoggedIn()) {
			$this->_view->loadLayout(); 
	    	$this->_view->renderLayout();
        }else{
			 $this->http->setRedirect($this->url->getUrl('customer/account/login'), 301);        	
        }
	}
	
}