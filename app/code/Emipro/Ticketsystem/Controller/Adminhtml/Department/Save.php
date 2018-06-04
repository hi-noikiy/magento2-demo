<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Department;

use Emipro\Ticketsystem\Model\TicketdepartmentFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

class Save extends Action {

    protected $_department;

    public function __construct(
    Context $context, Registry $registry, TicketdepartmentFactory $ticketdepartmentFactory
    ) {
        $this->_department = $ticketdepartmentFactory;
        parent::__construct($context);
    }

    public function execute() {
        $data = $this->getRequest()->getPost("department");
		$id=$this->getRequest()->getParam("department_id");
        $resultRedirect = $this->resultRedirectFactory->create();
        $back=$this->getRequest()->getParam("back");
        $department = $this->_department->create();
        if ($id) {
            $department->load($data["department_id"]);
        }
        $department->setData($data);
        try {
            $department->save();
            $this->messageManager->addSuccess(__('Department has been saved.'));
            if(isset($back))
			{
				$resultRedirect->setPath('emipro_ticketsystem/*/edit', ['department_id' => $department->getId(), '_current' => true]);
			}
			else{
				$resultRedirect->setPath('emipro_ticketsystem/*/');
			}
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            //echo $e->getMessage();
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $resultRedirect;
    }
}
