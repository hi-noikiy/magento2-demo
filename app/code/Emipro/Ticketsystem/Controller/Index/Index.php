<?php

namespace Emipro\Ticketsystem\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action {

    protected $resultPageFactory;
    protected $customerSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute() {
        if ($this->customerSession->isLoggedIn()) {
            $resultPage = $this->resultPageFactory->create();
            $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
            if ($navigationBlock) {
                $navigationBlock->setActive('ticketsystem/index/tickethistory');
            }
            return $resultPage;
        }
        $this->messageManager->addError(__('Please login to generate support ticket.'));
        return $this->resultRedirectFactory->create()->setPath('customer/account/login');
    }

}
