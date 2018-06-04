<?php

namespace Emipro\Ticketsystem\Controller\Index;

use Magento\Framework\App\Action\Action;
use Emipro\Ticketsystem\Helper\Data;

class View extends Action {

    protected $resultPageFactory;
    protected $helper;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, Data $helper, \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute() {
        $id = $this->getRequest()->getParam("id", 0);
        
        if ($this->helper->validUser($id)) {
			
            $resultPage = $this->resultPageFactory->create();
            $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
            if ($navigationBlock) {
                $navigationBlock->setActive('ticketsystem/index/tickethistory');
            }
            return $resultPage;
        } else {
            $this->messageManager->addError(__('Please login to generate support ticket.'));
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }
    }

}
