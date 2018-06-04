<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Emipro\Ticketsystem\Model\TicketSystemFactory;
//use Emipro\Ticketsystem\Helper\Data;

class Index extends Action {

    protected $_ticketFactory;
    protected $_resultPageFactory;
    //protected $_helper;

    public function __construct(Context $context, PageFactory $resultPageFactory, TicketSystemFactory $ticketFactory
        //,Data $helper
        ) {

        parent::__construct($context);
        $this->_ticketFactory = $ticketFactory;
        $this->_resultPageFactory = $resultPageFactory;
		//$this->_helper=$helper;
    }

    public function execute() {
        //$this->_helper->validTicketData();
        $ticket = $this->_ticketFactory->create();
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Emipro_Ticketsystem::manage_ticket');
        $resultPage->addBreadcrumb(__('Manage Ticket'), __('Manage Ticket'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Ticket'));
        return $resultPage;
    }

}
