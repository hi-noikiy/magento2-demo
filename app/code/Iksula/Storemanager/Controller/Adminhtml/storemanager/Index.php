<?php

namespace Iksula\Storemanager\Controller\Adminhtml\storemanager;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPagee;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Iksula_Storemanager::storemanager');
        $resultPage->addBreadcrumb(__('Iksula'), __('Iksula'));
        $resultPage->addBreadcrumb(__('Manage item'), __('Manage Storemanager'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Storemanager'));

        return $resultPage;
    }
}
?>