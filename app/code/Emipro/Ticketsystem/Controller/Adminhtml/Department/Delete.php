<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Department;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Emipro\Ticketsystem\Model\TicketdepartmentFactory;

class Delete extends Action {

    protected $_department;
    public function __construct(
    Context $context, 
    TicketdepartmentFactory $departmentFactory
    ) {
        $this->_department = $departmentFactory;
        parent::__construct($context);
    }

    public function execute() {
        $delete = 0;
        $resultRedirect = $this->resultRedirectFactory->create();
        $deleteId = $this->getRequest()->getParam("department_id");
        $department = $this->_department->create();
        try {
            if ($deleteId) {
                    $department->load($deleteId);
                    $department->delete();


                $this->messageManager->addSuccess(__('Department has been deleted.', $delete));
                $resultRedirect->setPath('emipro_ticketsystem/*/', ['_current' => true]);
                return $resultRedirect;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addSuccess(__('Can not delete department.'));
            $resultRedirect->setPath('emipro_ticketsystem/*/', ['_current' => true]);
            return $resultRedirect;
        }
    }

}
