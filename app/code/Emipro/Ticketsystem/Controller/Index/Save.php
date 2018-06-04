<?php

namespace Emipro\Ticketsystem\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Emipro\Ticketsystem\Model\TicketSystemFactory;
use Emipro\Ticketsystem\Model\TicketConversationFactory;
use Emipro\Ticketsystem\Model\TicketdepartmentFactory;
use Emipro\Ticketsystem\Model\TicketAttachmentFactory;
use Emipro\Ticketsystem\Helper\Email as EmailHelper;
use Emipro\Ticketsystem\Helper\Data;

class Save extends Action {

    protected $_ticket;
    protected $_conversation;
    protected $_formkey;
    protected $session;
    protected $_store;
    protected $context;
    protected $scopeConfig;
    protected $_department;
    protected $_emailHelper;
    protected $_attachment;
    protected $_transport;
    protected $_helper;

    public function __construct(
    Context $context, TicketSystemFactory $ticketFactory, TicketConversationFactory $ticketConversationFactory, TicketAttachmentFactory $attachmentFactory, Session $customerSession, StoreManagerInterface $storeManager, Validator $formKeyValidator, TicketdepartmentFactory $departmentFactory, EmailHelper $emailHelper, Data $helper, TransportBuilder $transportBuilder, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_ticket = $ticketFactory;
        $this->_conversation = $ticketConversationFactory;
        $this->_formkey = $formKeyValidator;
        $this->session = $customerSession;
        $this->_store = $storeManager;
        $this->_helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->_department = $departmentFactory;
        $this->_transport = $transportBuilder;
        $this->_emailHelper = $emailHelper;
        $this->_attachment = $attachmentFactory;
        $this->context = $context;
        parent::__construct($context);
    }

    public function execute() {
        $data = (array) $this->getRequest()->getPost();
        $files = $this->getRequest()->getFiles('file');
        if ($this->session->isLoggedIn()) {
            $customer_name = $this->session->getCustomer()->getName();
            $customerEmail = $this->session->getCustomer()->getEmail();
        }
        $storeId = $this->_store->getStore()->getId();
        if (!$this->session->isLoggedIn()) {
            $customer_name = $data["customer_name"];
            $customerEmail = $data["customer_email"];
            $websiteId = $this->_store->getStore()->getWebsiteId();
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
            $customer->setWebsiteId($websiteId)->loadByEmail($customerEmail);
            if ($customer->getId()) {
                $data["customer_id"] = $customer->getId();
            }
        }
        $externalId = md5(time() . $this->_store->getStore()->getId() . $customerEmail);
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $admin_user_id = $this->scopeConfig->getValue('emipro/emipro_group/ticket_admin', $storeScope);
        $ticket_email = $this->scopeConfig->getValue('emipro/emipro_group/ticket_email', $storeScope);
        $admin_email = $this->_objectManager->create('Magento\User\Model\User')->load($admin_user_id)->getEmail();
        if($this->scopeConfig->getValue('emipro/general/attachment_size', $storeScope)){
            $maxfile_size=1024 * 1024 * ((int)$this->scopeConfig->getValue('emipro/general/attachment_size', $storeScope));
        }else{
            $maxfile_size = 1024 * 1024 * 4; // 4 MB filesize;
        }
        $deptModel = $this->_department->create()->load($data["department_id"]);
        $currentDate = date('Y-m-d H:i', time());

        if ($files['size'] > $maxfile_size) {
            $this->messageManager->addError(__('Ticket attachment was not uploaded. Please,try again later.'));
            if (!$this->session->isLoggedIn()) {
                return $this->resultRedirectFactory->create()->setPath('ticketsystem/index/viewguest/', ["ticket_id" => $model->getExternalId(), "_secure" => true]);
            }
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        if ($data != "") {
            
            $assign_admin=$this->_objectManager->create('Magento\User\Model\User')->load($deptModel->getAdminUserId());
            $assign_admin_name=$assign_admin->getFirstname()." ".$assign_admin->getLastname();
            
            $model = $this->_ticket->create()->setData($data);
            $model->setData("admin_user_id", $admin_user_id);
            $model->setData("assign_admin_id", $deptModel->getAdminUserId());
            $model->setData("status_id", 1);
            $model->setData("date", $currentDate);
            $model->setData("lastupdated_date", $currentDate);
            $model->setData("store_id", $storeId);
            $model->setData("external_id", $externalId);
            $model->setData("unique_id", $this->_emailHelper->getUniqueTicketCode());
            $model->setData("sender_name",$assign_admin_name);
            try {
                $TicketId = $model->save()->getId();
                if ($TicketId != "") {
                    $con_model = $this->_conversation->create();
                    $con_model->setData("ticket_id", $TicketId);
                    $con_model->setData("message", $data["message"]);
                    $con_model->setData("name", $customer_name);
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
                    $isSave = $this->_helper->saveAttachment('file', $new_fileName, 'ticketsystem/attachment');
                    //$uploader->save($path . DS, $new_fileName );
                }
                $current_ticket_status = $this->_helper->getStatus($con_model->getStatusId());
                $ticket_template = $this->scopeConfig->getValue('emipro/template/ticket_create', $storeScope);
                $send_cc_email = $this->scopeConfig->getValue('emipro/emipro_group/superadmin_email', $storeScope);
                $subject = '[#' . $model->getUniqueId() . '-' . $TicketId . ']';
                /*$sender_email = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/email', $storeScope);
                $sender_name = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/name', $storeScope);*/
                $sender_email = $this->scopeConfig->getValue('emipro/emipro_emailgateway/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $sender_name = $this->scopeConfig->getValue('emipro/emipro_emailgateway/owner', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                $emailTemplateVariables = [];
                if (!$this->session->isLoggedIn()) {
                    $guestUrl = $this->_helper->getGuestTicketUrl(["ticket_id" => $model->getExternalId(), "_secure" => true]);
                    $emailTemplateVariables["guest_url"] = $guestUrl;
                }
                

                $emailTemplateVariables['customer_name'] = $customer_name;
                $emailTemplateVariables['message'] = nl2br($model->getMessage());
                $emailTemplateVariables['ticket_id'] = $TicketId;
                $emailTemplateVariables['ticket_status'] = $current_ticket_status;
                $emailTemplateVariables['tempsubject'] = $subject . " New support ticket with ticket Id " . $TicketId;
                $options = [
                    'area' => "frontend",
                    'store' => $storeId,
                ];
                /*                 * * Send Cc email *** */
                $cc = [];
                if ($send_cc_email == 1) {
                    $superAdmin = $this->scopeConfig->getValue('emipro/emipro_group/ticket_admin', $storeScope);
                    $super_admin_id = explode(",", $superAdmin);
                    $super_admin_email = [];
                    foreach ($super_admin_id as $id) {
                        if ($id != $deptModel->getAdminUserId()) {
                            $cc[] = $this->_objectManager->create('Magento\User\Model\User')->load($id)->getEmail();
                        }
                    }
                    //$emailTemplate->getMail()->addCc($super_admin_email);	
                }
                $this->_helper->sendTicketMail($sender_name, $sender_email, $admin_email, $ticket_template, $options, $emailTemplateVariables, $cc);


                // Ticket created successfully email for customer 

                $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($model->getCustomerId());
                $customer_email = $customer->getemail();
                $template = $this->scopeConfig->getValue('emipro/template/ticket_create_customer', $storeScope);
                /*$sender_email = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/email', $storeScope);
                $sender_name = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/name', $storeScope);*/
                $sender_email = $this->scopeConfig->getValue('emipro/emipro_emailgateway/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $sender_name = $this->scopeConfig->getValue('emipro/emipro_emailgateway/owner', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $emailTemplateVariables = [];
                $emailTemplateVariables['customer_name'] = $customer_name;
                $emailTemplateVariables['message'] = nl2br($model->getMessage());
                $emailTemplateVariables['ticket_id'] = $TicketId;
                if (!$this->session->isLoggedIn()) {
                    $emailTemplateVariables['guest_url'] = $guestUrl;
                }
                $emailTemplateVariables['tempsubject'] = $subject . " Support ticket has been created with Ticket Id " . $TicketId;
                $options = [
                    'area' => "frontend",
                    'store' => $storeId,
                ];
                $this->_helper->sendTicketMail($sender_name, $sender_email, $customerEmail, $template, $options, $emailTemplateVariables);

                $this->messageManager->addSuccess(__('Your support ticket has been created successfully with Ticket Id %1', $TicketId));
                if (!$this->session->isLoggedIn()) {
                    return $this->resultRedirectFactory->create()->setPath('ticketsystem/index/viewguest/', ["ticket_id" => $model->getExternalId(), "_secure" => true]);
                }
                return $this->resultRedirectFactory->create()->setPath('ticketsystem/*/tickethistory');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError(__('Unable to submit your request. Please, try again later.'));
                if (!$this->session->isLoggedIn()) {
                    return $this->resultRedirectFactory->create()->setPath('ticketsystem/index/viewguest/', ["ticket_id" => $model->getExternalId(), "_secure" => true]);
                }
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }

}
