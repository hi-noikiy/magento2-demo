<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Ticket;

use Emipro\Ticketsystem\Controller\Adminhtml\Ticket;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Emipro\Ticketsystem\Model\TicketSystemFactory;
use Magento\Framework\View\Result\PageFactory;

class View extends Ticket {

    protected $_coreRegistry = null;
    protected $_resultPageFactory;

    public function __construct(
    Context $context, TicketSystemFactory $ticketFactory, PageFactory $pageFactory, \Magento\Framework\Registry $registry
    ) {
        $this->_coreRegistry = $registry;
        $this->_resultPageFactory = $pageFactory;
        parent::__construct($context, $ticketFactory);
    }

    public function execute() {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Emipro_Ticketsystem::manage_ticket');
        $resultPage->addBreadcrumb(__('View Ticket'), __('View Ticket'));
        $resultPage->getConfig()->getTitle()->prepend(__('View Ticket'));
        return $resultPage;
    }

}
