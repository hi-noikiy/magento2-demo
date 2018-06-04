<?php

namespace Emipro\Ticketsystem\Controller\Index;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Emipro\Ticketsystem\Model\TicketSystemFactory;
use Emipro\Ticketsystem\Model\TicketConversationFactory;
use Emipro\Ticketsystem\Model\TicketdepartmentFactory;
use Emipro\Ticketsystem\Model\TicketAttachmentFactory;
use Emipro\Ticketsystem\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

class Savereply extends Action {

    protected $session;
    protected $scopeConfig;
    protected $_ticket;
    protected $_conversation;
    protected $_formkey;
    protected $_store;
    protected $_department;
    protected $_helper;
    protected $_fileUploaderFactory;
    protected $_attachment;
    protected $_filesystem;

    const TICKET_DIR = 'ticketsystem/attachment';

    public function __construct(
    Context $context, TicketSystemFactory $ticketFactory, TicketConversationFactory $ticketConversationFactory, Session $customerSession, StoreManagerInterface $storeManager, Validator $formKeyValidator, TicketdepartmentFactory $departmentFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory, Data $helper, TicketAttachmentFactory $ticketAttachmentFactory, Filesystem $Filesystem
    ) {
        $this->_ticket = $ticketFactory;
        $this->_conversation = $ticketConversationFactory;
        $this->_formkey = $formKeyValidator;
        $this->session = $customerSession;
        $this->_store = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_department = $departmentFactory;
        $this->_helper = $helper;
        $this->_attachment = $ticketAttachmentFactory;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_filesystem = $Filesystem;
        parent::__construct($context);
    }

    public function execute() {
        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $files = $this->getRequest()->getFiles('file');
        //$resultRedirect = $this->resultRedirectFactory->create();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $storeId = $this->_store->getStore()->getId();
        if($this->scopeConfig->getValue('emipro/general/attachment_size', $storeScope)){
			$maxfile_size=1024 * 1024 * ((int)$this->scopeConfig->getValue('emipro/general/attachment_size', $storeScope));
		}else{
			$maxfile_size = 1024 * 1024 * 4; // 4 MB filesize;
		}

        $data = (array) $this->getRequest()->getPost();
        $customer_name = $data["customer_name"];
        $customerSession = $this->session;
       
        $ticket_email = $this->scopeConfig->getValue('emipro/emipro_group/ticket_email', $storeScope);
        if ($data["message"] == "") {
            $this->messageManager->addError(__('Please enter valid message.'));
            if (!$customerSession->isLoggedIn()) {
                return $this->resultRedirectFactory->create()->setPath('ticketsystem/index/viewguest/', ["ticket_id" => $data['external_id'], "_secure" => true]);
            }
            return $this->resultRedirectFactory->create()->setPath('ticketsystem/*/view', ['id' => $data["ticket_id"], '_current' => true]);
        }
        if ($customerSession->isLoggedIn()) {
            $customer_name = $customerSession->getCustomer()->getName();
        }
        $ticket_system = $this->_ticket->create()->load($data["ticket_id"], "ticket_id");
        $admin_user_id = $ticket_system->getAdminUserId();
        $admin_email = $object_manager->get('Magento\User\Model\User')->load($admin_user_id)->getEmail();

        $assign_admin = $ticket_system->getAssignAdminId();
        $assign_admin_email = $object_manager->get('Magento\User\Model\User')->load($assign_admin)->getEmail();
        $subject = '[#' . $ticket_system->getUniqueId() . '-' . $data["ticket_id"] . ']';
        try {
            if ($files['size'] > $maxfile_size) {
                $customerSession->setMessage($data["message"]);
                $this->messageManager->addError('Ticket attachment was not uploaded. Please, try again later.');
                if (!$customerSession->isLoggedIn()) {
                    return $this->resultRedirectFactory->create()->setPath('ticketsystem/index/viewguest/', ["ticket_id" => $data['external_id'], "_secure" => true]);
                }
                return $this->resultRedirectFactory->create()->setPath('ticketsystem/*/view', ['id' => $data["ticket_id"], '_current' => true]);
            }

            $status = ["status_id" => $data["status"], "lastupdated_date" => $data["date"]];
            $ticket_system->addData($status);
            $ticket_system->setId($data["ticket_id"])->save();
            $TicketId = $ticket_system->save()->getId();

            $model = $this->_conversation->create();
            $model->setData("ticket_id", $data["ticket_id"]);
            $model->setData("message", $data["message"]);
            $model->setData("name", $customer_name);
            $model->setData("status_id", $data["status"]);
            $model->setData("date", $data["date"]);
            $model->setData("store_id", $storeId);
            $model->save();
            $conversation_id = $model->save()->getId();

            if (isset($files['name']) && $files['name'] != '') {
                $fileName = $files['name'];
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $new_fileName = md5(uniqid(rand(), true)) . "." . $ext;
                $uploader = $this->_fileUploaderFactory->create(['fileId' => 'file']);
                $path = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                        ->getAbsolutePath('ticketsystem/attachment');
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                $uploader->save($path, $new_fileName);
                $file = $this->_attachment->create();
                $file->setData("conversation_id", $conversation_id);
                $file->setData("file", $new_fileName);
                $file->setData("current_file_name", $fileName);
                $file->setData("store_id", $storeId);
                $file->save();
                $file_id = $file->save()->getId();
                $customerSession->unsTicketmessage();
            }
            $current_ticket_status = $this->_helper->getStatus($model->getStatusId());
            $ticket_template = $this->scopeConfig->getValue('emipro/template/ticket_conversation', $storeScope);
            $send_cc_email = $this->scopeConfig->getValue('emipro/emipro_group/superadmin_email', $storeScope);
            
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

                $cc = [];
                if ($send_cc_email == 1) {
                    $superAdmin = $this->scopeConfig->getValue('emipro/emipro_group/ticket_admin', $storeScope);
                    $super_admin_id = explode(",", $superAdmin);
                    $super_admin_email = [];
                    foreach ($super_admin_id as $id) {
                            $cc[] = $this->_objectManager->create('Magento\User\Model\User')->load($id)->getEmail();
                    }
                    //$emailTemplate->getMail()->addCc($super_admin_email); 
                }
            
            $this->_helper->sendTicketMail($sender_name, $sender_email, $admin_email, $ticket_template, $options, $emailTemplateVariables, $cc);
            $this->_helper->sendTicketMail($sender_name, $sender_email, $assign_admin_email, $ticket_template, $options, $emailTemplateVariables);


            $this->messageManager->addSuccess(__('Ticket has been updated successfully.'));
            if (!$customerSession->isLoggedIn()) {
				return $this->resultRedirectFactory->create()->setPath('ticketsystem/*/viewguest', ["ticket_id" => $ticket_system->getExternalId()]);
			}
			return $this->resultRedirectFactory->create()->setPath('ticketsystem/*/view', ['id' => $data["ticket_id"], '_current' => true]);
       
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            //echo $e->getMessage();
            $this->messageManager->addError(__('Unable to submit your request. Please, try again later.'));
             if (!$customerSession->isLoggedIn()) {
				return $this->resultRedirectFactory->create()->setPath('ticketsystem/*/viewguest', ["ticket_id" => $ticket_system->getExternalId()]);
			}
			return $this->resultRedirectFactory->create()->setPath('ticketsystem/*/view', ['id' => $data["ticket_id"], '_current' => true]);
        }
    }

}
