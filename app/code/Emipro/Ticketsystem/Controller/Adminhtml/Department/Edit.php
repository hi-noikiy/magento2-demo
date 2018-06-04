<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Department;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Edit extends Action {

    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
    Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    public function execute() {
        $id = $this->getRequest()->getParam('department_id');
        $model = $this->_objectManager->create('Emipro\Ticketsystem\Model\Ticketdepartment');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This Department no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('department', $model->getData());

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
                $id ? __('Edit Department') : __('New Department'), $id ? __('Edit Department') : __('New Department')
        );
      
        $resultPage->getConfig()->getTitle()
                ->prepend($model->getId() ? $model->getDepartmentName() : __('New Department'));

        return $resultPage;
    }

    protected function _initAction() {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Emipro_Ticketsystem::manage_department')
                ->addBreadcrumb(__('Ticketsystem'), __('Ticketsystem'))
                ->addBreadcrumb(__('Manage Department'), __('Manage Department'));
        return $resultPage;
    }

}
