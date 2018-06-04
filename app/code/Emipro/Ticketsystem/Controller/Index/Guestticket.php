<?php

namespace Emipro\Ticketsystem\Controller\Index;

use Magento\Backend\Model\View\Result\ForwardFactory;

class Guestticket extends \Magento\Framework\App\Action\Action {

    protected $resultPageFactory;
    protected $customerSession;
    protected $forward;
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, ForwardFactory $resultForwardFactory
    ) {
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    public function execute() {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $allowGuest = $this->scopeConfig->getValue('emipro/general/allow_guest', $storeScope);
        if ($allowGuest) {
            if (!$this->customerSession->isLoggedIn()) {
                $resultPage = $this->resultPageFactory->create();
                return $resultPage;
            }
            return $this->resultRedirectFactory->create()->setPath('ticketsystem/index/');
        }
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('cms/noroute/index');
    }

}
