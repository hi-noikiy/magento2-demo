<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Response;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Emipro\Ticketsystem\Model\TicketresponseFactory;
//use Emipro\Ticketsystem\Helper\Data;

class Index extends Action 
{

    protected $resultPageFactory;
    protected $_modelResponseFactory;
    //protected $_helper;

    public function __construct(
    Context $context, PageFactory $resultPageFactory, TicketresponseFactory $responseFactory
    //,Data $helper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_modelResponseFactory = $responseFactory;
        //$this->_helper=$helper;
    }

    public function execute() {
		//$this->_helper->validTicketData();
        $department = $this->_modelResponseFactory->create();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Emipro_Ticketsystem::manage_response');
        $resultPage->addBreadcrumb(__('Manage Department'), __('Manage Response'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Frequent Response'));
        return $resultPage;
    }

}
