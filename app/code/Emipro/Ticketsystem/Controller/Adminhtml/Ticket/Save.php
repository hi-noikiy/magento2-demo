<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Mail\Message;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportInterfaceFactory;
use Zend_Mail;
use Emipro\Ticketsystem\Controller\Adminhtml\Ticket;
use Emipro\Ticketsystem\Model\TicketSystemFactory;
use Emipro\Ticketsystem\Model\TicketConversationFactory;
use Emipro\Ticketsystem\Model\TicketdepartmentFactory;
use Emipro\Ticketsystem\Model\TicketAttachmentFactory;

class Save extends Action {

    protected $_coreRegistry = null;
    protected $_resultPageFactory;
    protected $_fileUploaderFactory;
    protected $_ticket;
    protected $_conversation;
    protected $_store;
    protected $scopeConfig;
    protected $_department;
    protected $_appEmulation;
    protected $helper;
    protected $_attachment;
    protected $_transport;
    protected $_emailHelper;

    public function __construct(
    Context $context, TicketSystemFactory $ticketFactory, TicketConversationFactory $ticketConversationFactory, StoreManagerInterface $storeManager, TicketdepartmentFactory $departmentFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory, PageFactory $pageFactory, \Magento\Framework\Registry $registry, \Magento\Store\Model\App\Emulation $appEmulation, \Emipro\Ticketsystem\Helper\Data $helper, \Emipro\Ticketsystem\Helper\Email $emailHelper, TicketAttachmentFactory $attachmentFactory, TransportBuilder $transportBuilder, Filesystem $Filesystem, Message $message
    ) {
        $this->_coreRegistry = $registry;
        $this->_resultPageFactory = $pageFactory;
        $this->_appEmulation = $appEmulation;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_filesystem = $Filesystem;
        $this->_message = $message;
        $this->_emailHelper = $emailHelper;
        $this->_ticket = $ticketFactory;
        $this->_conversation = $ticketConversationFactory;
        $this->_store = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->_attachment = $attachmentFactory;

        $this->_department = $departmentFactory;
        $this->_transport = $transportBuilder;
        parent::__construct($context);
    }

    public function execute() {

        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $files = $this->getRequest()->getFiles('file');
        $admin_id = $object_manager->get('\Magento\Backend\Model\Auth\Session')->getUser()->getUserId();
        $resultRedirect = $this->resultRedirectFactory->create();
        $current_status = "";
        $file_id = "";
        if ($this->getRequest()->getPost()) {
            try {
                $data = (array) $this->getRequest()->getPost();
                if ($data["message"] == "" && !$this->getRequest()->getParam("assign") && !$this->getRequest()->getParam("sendmsg")) {
                    $this->messageManager->addError(__('Please enter message.'));
                    $resultRedirect->setPath('emipro_ticketsystem/*/view', ['ticket_id' => $data["ticket_id"], '_current' => true]);
                    return $resultRedirect;
                }
                if($this->getRequest()->getParam("assign") && $data["admin_id"]==""){
					$this->messageManager->addError(__('Please Select Assignee Name.'));
                    $resultRedirect->setPath('emipro_ticketsystem/*/view', ['ticket_id' => $data["ticket_id"],'tab'=>'ofc', '_current' => true]);
                    return $resultRedirect;
				}
				if($this->getRequest()->getParam("sendmsg") && ($data["admin_id"]==""  || $data["assign_msg"]=="")){
					$this->messageManager->addError(__('Please Select admin name and enter message.'));
                    $resultRedirect->setPath('emipro_ticketsystem/*/view', ['ticket_id' => $data["ticket_id"],'tab'=>'ofc', '_current' => true]);
                    return $resultRedirect;
				}
                $ticket_system = $this->_ticket->create()->load($data["ticket_id"], "ticket_id");
                $storeId = $ticket_system->getStoreId();
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                
				if($this->scopeConfig->getValue('emipro/general/attachment_size', $storeScope)){
					$maxfile_size=1024 * 1024 * ((int)$this->scopeConfig->getValue('emipro/general/attachment_size', $storeScope));
				}else{
					$maxfile_size = 1024 * 1024 * 4; // 4 MB filesize;
				}
                
                $ticket_email = $this->scopeConfig->getValue('emipro/emipro_group/ticket_email', $storeScope,$storeId);
                //$ticket_admin_name = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/name', $storeScope,$storeId);
                $ticket_admin_name = $this->scopeConfig->getValue('emipro/emipro_emailgateway/owner', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($files['size'] > $maxfile_size) {
                    $this->_getSession()->setTicketmessage($data["message"]);
                    $this->messageManager->addError(__('Ticket attachment was not uploaded. Please,try again later.'));
                    $resultRedirect->setPath('emipro_ticketsystem/*/view', ['ticket_id' => $data["ticket_id"], '_current' => true]);
                    return $resultRedirect;
                }
				if($files['name']!='' && $files['size']==0){
					//$this->_getSession()->setTicketmessage($data["message"]);
                    $this->messageManager->addError(__('Ticket attachment was not uploaded. Please,try again later.'));
                    $resultRedirect->setPath('emipro_ticketsystem/*/view', ['ticket_id' => $data["ticket_id"], '_current' => true]);
                    return $resultRedirect;
				}
                if ($this->getRequest()->getParam("assign") || $this->getRequest()->getParam("sendmsg")) {

                    $current_status = $data["assign_status"];
                } else {
                    $current_status = $data["status"];
                }
                $status = ["status_id" => $current_status, "assign_admin_id" => $ticket_system->getAssignAdminId(), "lastupdated_date" => $data["date"]];
                $ticket_system->addData($status);
                $ticket_system->setId($data["ticket_id"])->save();
                $subject = '[#' . $ticket_system->getUniqueId() . '-' . $data["ticket_id"] . ']';
                $model = $this->_conversation->create();
                $this->_appEmulation->startEnvironmentEmulation($storeId);
               
				$currentAdmin=$object_manager->get('Magento\User\Model\User')->load($data["admin_id"]);
				$currentAdminName=$currentAdmin->getFirstname()." ".$currentAdmin->getLastname();
				$current_admin_info=$object_manager->get('Magento\User\Model\User')->load($admin_id);
				$current_admin_name=$current_admin_info->getFirstname()." ".$current_admin_info->getLastname();
					
                if (($data["assign_msg"] != "" || $data["admin_id"] != "") && $this->getRequest()->getParam("assign")) {
                    $assign_id = ["assign_admin" => $data["admin_id"]];
                    $ticket_system->addData($assign_id);
                    $ticket_system->setId($data["ticket_id"])->save();

                    $model->setData("message", $data["assign_msg"]);
                    $model->setData("message_type", "official");
                    $model->setData("ticket_id", $data["ticket_id"]);
                    $model->setData("name", $data["name"]);
                    $model->setData("date", $data["date"]);
                    $model->setData("status_id", $current_status);
                    $model->setData("store_id", $storeId);
                    $model->setData("current_admin", $data["admin_id"]);
                    $model->setData("current_admin_name",$currentAdminName);
                    $model->save();
                    $ticket_assign = ["assign_admin_id" => $data["admin_id"],"sender_name"=>$currentAdminName];
                    $ticket_system->addData($ticket_assign);
                    $ticket_system->setId($data["ticket_id"])->save();

                    // assign ticket email

                    $assign_admin_email = $object_manager->get('Magento\User\Model\User')->load($data["admin_id"])->getEmail();
                    $ticket_template = $this->scopeConfig->getValue('emipro/template/ticket_assign', $storeScope,$storeId);
                    //$sender_email = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/email', $storeScope,$storeId);
                    $sender_email = $this->scopeConfig->getValue('emipro/emipro_emailgateway/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                
                    $options = [
                        'area' => "frontend",
                        'store' => $storeId,
                    ];
                    $emailTemplateVariables = [];
                    $emailTemplateVariables['message'] = nl2br($model->getMessage());
                    $emailTemplateVariables['ticket_id'] = $data["ticket_id"];
                    $emailTemplateVariables["tempsubject"] = "Support ticket " . $data["ticket_id"] . " assigned to you";
                    $this->helper->sendTicketMail($ticket_admin_name, $sender_email, $assign_admin_email, $ticket_template, $options, $emailTemplateVariables);
                }
                /*start by ept 68 */
                if(($data["assign_msg"]!="" || $data["admin_id"]!="") && $this->getRequest()->getParam("sendmsg"))
				{
					
					$model->setData("message",$data["assign_msg"]);
					$model->setData("message_type","internal");
					$model->setData("ticket_id",$data["ticket_id"]);
					$model->setData("name",$data["name"]);
					$model->setData("date",$data["date"]);
					$model->setData("status_id",$current_status);
					$model->setData("store_id",$storeId);
					$model->setData("current_admin",$admin_id);
					$model->setData("current_admin_name",$current_admin_name);
					$model->setData("discussion_admin",$data["admin_id"]);
					$model->setData("discussion_admin_name",$currentAdminName);
					$model->save();
					
					//send a message email
					$sender_admin_email = $object_manager->get('Magento\User\Model\User')->load($data["admin_id"])->getEmail();
                    $ticket_template = $this->scopeConfig->getValue('emipro/template/ticket_sendmsg', $storeScope,$storeId);
                    /*$sender_email = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/email', $storeScope,$storeId);*/
                    $sender_email = $this->scopeConfig->getValue('emipro/emipro_emailgateway/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $options = ['area' => "frontend",'store' => $storeId,];
                    $emailTemplateVariables = [];
                    $emailTemplateVariables['message'] = nl2br($model->getMessage());
                    $emailTemplateVariables['ticket_id'] = $data["ticket_id"];
                    $emailTemplateVariables['admin_name']=$currentAdminName;
                    $emailTemplateVariables["tempsubject"] =$subject."Message received from".$current_admin_name."on Ticket".$data['ticket_id'];
                    $this->helper->sendTicketMail($ticket_admin_name, $sender_email, $sender_admin_email, $ticket_template, $options, $emailTemplateVariables);

					$this->messageManager->addSuccess(__('Message successfully Send.'));
                    return $resultRedirect->setPath('emipro_ticketsystem/*/view', ['ticket_id' => $data["ticket_id"],'tab'=>'ofc', '_current' => true]);
				}
                /*end */
                
                $file_id = "";
                if ($this->getRequest()->getParam("assign")) {
                    $this->messageManager->addSuccess(__('Ticket successfully Assign.'));
                    return $resultRedirect->setPath('emipro_ticketsystem/*/view', ['ticket_id' => $data["ticket_id"],'tab'=>'ofc', '_current' => true]);
                }

                $model->setData($data);
                $model->setData("name", $data["name"]);
                $model->setData("status_id", $current_status);
                $model->setData("current_admin", $admin_id);
                $model->setData("current_admin_name",$current_admin_name);
                $model->setData("store_id", $storeId);

                $con_id = $model->save()->getId();
                $current_ticket_status = $this->helper->getStatus($model->getStatusId());

                if (isset($files['name']) && $files['name'] != '') {
                    $fileName = $files['name'];
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    $new_fileName = md5(uniqid(rand(), true)) . "." . $ext;
                    $isSave = $this->helper->saveAttachment('file', $new_fileName, 'ticketsystem/attachment');
                    $file = $this->_attachment->create();
                    $file->setData("conversation_id", $con_id);
                    $file->setData("file", $new_fileName);
                    $file->setData("current_file_name", $fileName);
                    $file->setData("store_id", $storeId);
                    $file->save();
                    $file_id = $file->save()->getId();
                    $this->_getSession()->unsTicketmessage();
                }

                // ticket conversation mail send to customer.
                $TicketId = $data["ticket_id"];
                $customer_email = $ticket_system->getCustomerEmail();
                $customer_name = $ticket_system->getCustomerName();
                if ($data["customer_id"]) {
                    $customer = $object_manager->get("Magento\Customer\Model\Customer")->load($data["customer_id"]);
                    $customer_email = $customer->getEmail();
                    $customer_name = $customer->getName();
                }
                $ticket_template = $this->scopeConfig->getValue('emipro/template/ticket_conversation', $storeScope,$storeId);
                /*$sender_email = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/email', $storeScope,$storeId);*/
                $sender_email = $this->scopeConfig->getValue('emipro/emipro_emailgateway/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $emailTemplateVariables = [];
                $emailTemplateVariables['seprator'] = $this->_emailHelper->getSeprator();
                $emailTemplateVariables['customer_name'] = $customer_name;
                $emailTemplateVariables['message'] = $model->getMessage();
                $emailTemplateVariables['ticket_id'] = $data["ticket_id"];
                $emailTemplateVariables['sender_name'] = $ticket_admin_name;
                /*$sender_name = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/name', $storeScope,$storeId);*/
                $sender_name = $this->scopeConfig->getValue('emipro/emipro_emailgateway/owner', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $emailTemplateVariables['ticket_status'] = $current_ticket_status;
                $emailTemplateVariables['tempsubject'] = $subject . " Reply for support ticket " . $data["ticket_id"];
                $options = [
                    'area' => "frontend",
                    'store' => $storeId,
                ];
                $this->helper->sendTicketMail($ticket_admin_name, $sender_email, $customer_email, $ticket_template, $options, $emailTemplateVariables);
                $this->_appEmulation->stopEnvironmentEmulation();

                if ($this->getRequest()->getParam("back")) {
                    $this->messageManager->addSuccess(__('Ticket has been updated successfully.'));
                    return $resultRedirect->setPath('emipro_ticketsystem/*/view', ['ticket_id' => $data["ticket_id"], '_current' => true]);
                }

                $this->messageManager->addSuccess(__('Ticket information has been saved successfully.'));
                return $resultRedirect->setPath('emipro_ticketsystem/*/view', ['ticket_id' => $data["ticket_id"], '_current' => true]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addSuccess(__('Unable to submit your request. Please, try again later.'));
                return $resultRedirect->setPath('emipro_ticketsystem/*/view', ['ticket_id' => $data["ticket_id"], '_current' => true]);
            }
        }
    }

}
