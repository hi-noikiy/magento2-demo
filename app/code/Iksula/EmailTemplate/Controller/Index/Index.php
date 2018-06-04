<?php

namespace Iksula\EmailTemplate\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $inlineTranslation;

    public function execute()
    { 
    	$this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();        
    }
}