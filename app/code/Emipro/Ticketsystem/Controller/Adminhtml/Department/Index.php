<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Department;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Emipro\Ticketsystem\Model\TicketdepartmentFactory;
//use Emipro\Ticketsystem\Helper\Data;

class Index extends Action
{

    protected $resultPageFactory;
    protected $_modelDepartmentFactory;
    //protected $_helper;

    public function __construct(
    Context $context, PageFactory $resultPageFactory, TicketdepartmentFactory $departmentFactory
    //,Data $helper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_modelDepartmentFactory = $departmentFactory;
        //$this->_helper=$helper;
    }

    public function execute() {
		//$this->_helper->validTicketData();
        $department = $this->_modelDepartmentFactory->create();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Emipro_Ticketsystem::manage_department');
        $resultPage->addBreadcrumb(__('Manage Department'), __('Manage Department'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Department'));
        return $resultPage;
    }

}
