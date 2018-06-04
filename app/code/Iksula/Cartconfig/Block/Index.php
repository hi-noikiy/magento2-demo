<?php

namespace Iksula\Cartconfig\Block;
use Magento\Framework\View\Element\Template\Context;


Class Index extends \Magento\Framework\View\Element\Template
{

	public function __construct(Context $context, array $data = array())
    {
        parent::__construct($context,$data);
    }

    protected function _prepareLayout()
    {	
    	return parent::_prepareLayout();
         
    }

    public function getCartNote(){

    	//return 'terss';
    	$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    	$note = $this->_scopeConfig->getValue('cart_section/general/cart_note', $storeScope);
    	return $note;
    }
}