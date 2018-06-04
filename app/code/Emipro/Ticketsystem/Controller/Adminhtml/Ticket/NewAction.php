<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;

class NewAction extends Action {

    public function __construct(
    Context $context, PageFactory $resultPageFactory, Rawfactory $resultRawFactory, LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute() {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Emipro_Ticketsystem::manage_ticket');
        $resultPage->addBreadcrumb(__('Select Customer'), __('Select Customer'));
        $resultPage->getConfig()->getTitle()->prepend(__('Select Customer'));
        return $resultPage;
    }

}
