<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class TicketCreate extends Action {

    protected $_resultPageFactory;

    public function __construct(
    Context $context, PageFactory $_resultPageFactory
    ) {
        $this->_resultPageFactory = $_resultPageFactory;
        parent::__construct($context);
    }

    public function execute() {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Emipro_Ticketsystem::manage_ticket');
        $resultPage->addBreadcrumb(__('New Ticket'), __('New Ticket'));
        $resultPage->getConfig()->getTitle()->prepend(__('New Ticket'));
        return $resultPage;
    }

}
