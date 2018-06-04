<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Emipro\Ticketsystem\Model\TicketSystemFactory;
use Emipro\Ticketsystem\Model\TicketConversationFactory;
use Emipro\Ticketsystem\Model\TicketdepartmentFactory;
use Emipro\Ticketsystem\Model\TicketAttachmentFactory;

use Emipro\Ticketsystem\Helper\Email as EmailHelper;
use Emipro\Ticketsystem\Helper\Data;

class NewTicket extends Action {

    protected $_resultPageFactory;
    protected $_ticket;
    protected $_conversation;
    protected $_formkey;
    protected $session;
    protected $_store;
    protected $scopeConfig;
    protected $_department;
    protected $_emailHelper;
    protected $_attachment;
    protected $_transport;
    protected $_tickethelper;

    public function __construct(
    Context $context, PageFactory $_resultPageFactory, TicketSystemFactory $ticketFactory, TicketConversationFactory $ticketConversationFactory, TicketAttachmentFactory $attachmentFactory, StoreManagerInterface $storeManager, TicketdepartmentFactory $departmentFactory, EmailHelper $emailHelper, Data $helper, \Magento\Store\Model\App\Emulation $appEmulation, TransportBuilder $transportBuilder, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_resultPageFactory = $_resultPageFactory;

        $this->_ticket = $ticketFactory;
        $this->_conversation = $ticketConversationFactory;
        $this->_store = $storeManager;
        $this->_appEmulation = $appEmulation;
        $this->_tickethelper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->_department = $departmentFactory;
        $this->_transport = $transportBuilder;
        $this->_emailHelper = $emailHelper;
        $this->_attachment = $attachmentFactory;

        parent::__construct($context);
    }

    public function execute() {
        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $emailHelper = $this->_emailHelper;
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $files = $this->getRequest()->getFiles('file');
        $file_id = "";
        if($this->scopeConfig->getValue('emipro/general/attachment_size', $storeScope)){
			$maxfile_size=1024 * 1024 * ((int)$this->scopeConfig->getValue('emipro/general/attachment_size', $storeScope));
		}else{
			$maxfile_size = 1024 * 1024 * 4; // 4 MB filesize;
		}
        $data = (array) $this->getRequest()->getPost();
        $adminName = $this->_tickethelper->getCurrentAdminName();
        $store = $this->_tickethelper->getCustomerStore($data["customer_id"]);
        $storeId = $store->getId();
        $this->_appEmulation->startEnvironmentEmulation($storeId);
        
        $admin_user_id = $this->scopeConfig->getValue('emipro/emipro_group/ticket_admin', $storeScope);
        $admin=$object_manager->get('Magento\User\Model\User')->load($admin_user_id);
        $admin_email = $admin->getEmail();
        $admin_name=$admin->getFirstname()." ".$admin->getLastname();
        $ticket_email = $this->scopeConfig->getValue('emipro/emipro_group/ticket_email', $storeScope);
        /*$ticket_admin_name = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/name', $storeScope);*/
        $ticket_admin_name = $this->scopeConfig->getValue('emipro/emipro_emailgateway/owner', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $currentDate = date('Y-m-d H:i', time());
        $customer_name = $this->_tickethelper->getCustomerName($data["customer_id"]);
        $deptModel = $this->_department->create()->load($data["department_id"]);

        if (isset($files) && $files['size'] > $maxfile_size) {
            $this->messageManager->addError(__('Ticket attachment was not uploaded. Please,try again later.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        if ($files['name']!='' && $files['size']==0) {
            $this->messageManager->addError(__('Ticket attachment was not uploaded. Please,try again later.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        
        if ($data != "") {
			
			$assign_admin=$object_manager->get('Magento\User\Model\User')->load($deptModel->getAdminUserId());
			$assign_admin_name=$assign_admin->getFirstname()." ".$assign_admin->getLastname();
			
            $model = $this->_ticket->create()->setData($data);
            $model->setData("admin_user_id", $admin_user_id);
            $model->setData("assign_admin_id", $deptModel->getAdminUserId());
            $model->setData("customer_id", $data["customer_id"]);
            $model->setData("status_id", 1);
            $model->setData("date", $currentDate);
            $model->setData("lastupdated_date", $currentDate);
            $model->setData("unique_id", $emailHelper->getUniqueTicketCode());
            $model->setData("store_id", $storeId);
            $model->setData("sender_name",$assign_admin_name);

            try {
                $TicketId = $model->save()->getId();
                if ($TicketId != "") {
                    $con_model = $this->_conversation->create();
                    $con_model->setData("ticket_id", $TicketId);
                    $con_model->setData("message", $data["message"]);
                    $con_model->setData("name", $adminName);
                    $con_model->setData("current_admin", $admin_user_id);
                    $con_model->setData("current_admin_name",$admin_name);
                    $con_model->setData("status_id", 1);
                    $con_model->setData("date", $currentDate);
                    $con_model->setData("store_id", $storeId);
                    $con_model->save();
                    $con_id = $con_model->save()->getId();
                }

                if (isset($files['name']) && $files['name'] != '') {
                    $fileName = $files['name'];
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    $new_fileName = md5(uniqid(rand(), true)) . "." . $ext;

                    $file = $this->_attachment->create();
                    $file->setData("conversation_id", $con_id);
                    $file->setData("file", $new_fileName);
                    $file->setData("current_file_name", $fileName);
                    $file->save();
                    $file_id = $file->save()->getId();
                    $isSave = $this->_tickethelper->saveAttachment('file', $new_fileName, 'ticketsystem/attachment');
                }

                $current_ticket_status = $this->_tickethelper->getStatus($con_model->getStatusId());

                // Ticket created successfully email for customer 
                $customer = $object_manager->get("Magento\Customer\Model\Customer")->load($model->getCustomerId());
                $customer_email = $customer->getemail();

                $ticket_template = $this->scopeConfig->getValue('emipro/template/ticket_create_customer', $storeScope);
                $subject = '[#' . $model->getUniqueId() . '-' . $TicketId . ']';
                /*$sender_email = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/email', $storeScope);
                $sender_name = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/name', $storeScope);*/
                $sender_email = $this->scopeConfig->getValue('emipro/emipro_emailgateway/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $sender_name = $this->scopeConfig->getValue('emipro/emipro_emailgateway/owner', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $emailTemplateVariables = [];
                $emailTemplateVariables['customer_name'] = $customer_name;
                $emailTemplateVariables['message'] = nl2br($model->getMessage());
                $emailTemplateVariables['ticket_id'] = $TicketId;
                $emailTemplateVariables['tempsubject'] = $subject . " Support ticket has been created with Ticket Id " . $TicketId;
                $options = [
                    'area' => "frontend",
                    'store' => $storeId,
                ];
                $this->_tickethelper->sendTicketMail($sender_name, $sender_email, $customer_email, $ticket_template, $options, $emailTemplateVariables);
                $this->_appEmulation->stopEnvironmentEmulation();
                $this->messageManager->addSuccess(__('Support ticket has been created successfully with ticket Id  ' . $TicketId));
                return $this->resultRedirectFactory->create()->setPath('emipro_ticketsystem/*/index');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError(__('Unable to submit your request. Please, try again later.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }

}
