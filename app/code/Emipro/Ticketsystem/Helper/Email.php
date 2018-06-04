<?php

namespace Emipro\Ticketsystem\Helper;

use Emipro\Ticketsystem\Helper\Parse;
use Emipro\Ticketsystem\Helper\Data;
use Emipro\Ticketsystem\Model\TicketdepartmentFactory;
use Emipro\Ticketsystem\Model\TicketPriorityFactory;
use Emipro\Ticketsystem\Model\TicketStatusFactory;
use Emipro\Ticketsystem\Model\TicketSystemFactory;
use Emipro\Ticketsystem\Model\TicketConversationFactory;
use Emipro\Ticketsystem\Model\TicketAttachmentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem;

class Email extends \Magento\Framework\App\Helper\AbstractHelper {

    public $inbox;
    protected $_parser;
    protected $_helper;
    protected $_department;
    protected $_status;
    protected $_store;
    protected $_priority;
    protected $_logger;
    protected $_ticketSystem;
    protected $_conversation;
    protected $_attachment;
    protected $_appEmulation;
    protected $_filesystem;

    public function __construct(
    \Magento\Framework\App\Helper\Context $context, Parse $parse, TicketdepartmentFactory $TicketdepartmentFactory, TicketPriorityFactory $TicketPriorityFactory, TicketAttachmentFactory $attachmentFactory, TicketStatusFactory $TicketStatusFactory, StoreManagerInterface $storeManager, TicketSystemFactory $ticketFactory, Filesystem $filesystem, Data $helper, \Magento\Store\Model\App\Emulation $appEmulation, TicketConversationFactory $ticketConversationFactory
    ) {
        $this->_parser = $parse;
        $this->_department = $TicketdepartmentFactory;
        $this->_conversation = $ticketConversationFactory;
        $this->_status = $TicketStatusFactory;
        $this->_store = $storeManager;
        $this->_priority = $TicketPriorityFactory;
        $this->_filesystem = $filesystem;
        $this->_ticketSystem = $ticketFactory;
        $this->_appEmulation = $appEmulation;
        $this->_attachment = $attachmentFactory;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    public function connect($host, $email, $password) {
        if (!function_exists('imap_open')) {
            throw new \Magento\Framework\Exception\LocalizedException(
            __('Can\'t fetch. Please, ask your hosting provider to enable IMAP extension in PHP configuration of your server.'));
        }
        try {
            $mbox = imap_open($host, $email, $password);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return false;
        }

        return $mbox;
    }

    public function isAttachment($structure) {
        if (isset($structure->parts) && count($structure->parts)) {
            return true;
        }
        return false;
    }

    public function getSeprator() {
        return "----------Please reply above this line----------";
    }

    public function getUniqueTicketCode() {
        return $this->generateRandString(6);
    }

    public function getIdFromSubject($subject) {
        if ($subject && preg_match('[[#][A-Z0-9]{1,6}-(?<id>\d+)]', $subject, $ticketId)) {
            return $ticketId["id"];
        }
    }

    public function generateRandNum($length) {
        $characters = '0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    public function generateRandString($length) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    public function getMails($hostname, $username, $password) {
        $newEmails = [];
        $message = "";
        $attachments = [];
        $imageData = [];
        $this->inbox = $this->connect($hostname, $username, $password);
        if ($this->inbox) {
            $emails = imap_search($this->inbox, 'UNSEEN');
            //if there is a message in your inbox
            if ($emails) {
                foreach ($emails as $email_number) {
                    $structures = imap_fetchstructure($this->inbox, $email_number);
                    if (isset($structures->parts)) {
                        $flattenedParts = $this->_parser->flattenParts($structures->parts);
                        foreach ($flattenedParts as $partNumber => $part) {
                            switch ($part->type) {
                                case 0:
                                    // the HTML or plain text part of the email
                                    if ($part->subtype == "PLAIN") {
                                        $message = $this->_parser->getPart($this->inbox, $email_number, $partNumber, $part->encoding);
                                    }
                                    // now do something with the message, e.g. render it
                                    break;

                                case 1:
                                    // multi-part headers, can ignore

                                    break;
                                case 2:
                                    // attached message headers, can ignore
                                    break;

                                case 3: // application
                                case 4: // audio
                                case 5: // image
                                    $imageData[$partNumber] = $this->_parser->getPart($this->inbox, $email_number, $partNumber, $part->encoding);
                                case 6: // vide					
                            }
                        }
                    } else {
                        $message = $this->_parser->getPart($this->inbox, $email_number, "1", $structures->encoding);
                    }
                    if ($this->isAttachment($structures)) {
                        $attachments = $this->getAttachments($flattenedParts, $this->inbox, $email_number, $imageData);
                    }
                    $newEmails[$email_number] = ["header" => imap_headerinfo($this->inbox, $email_number),
                        "body" => imap_fetchbody($this->inbox, $email_number, "3"),
                        "structure" => $structures,
                        "message" => $message,
                        "attachment" => $attachments];
                }
            }
            //close the stream
            imap_close($this->inbox);
            return $newEmails;
        }
    }

    public function getAttachments($structures, $inbox, $email_number, $imageData) {
        if (isset($structures) && count($structures)) {
            foreach ($structures as $i => $structure) {
                $attachments[$i] = [
                    'is_attachment' => false,
                    'filename' => '',
                    'name' => '',
                    'attachment' => ''
                ];

                if ($structure->ifdparameters) {
                    foreach ($structure->dparameters as $object) {
                        if (strtolower($object->attribute) == 'filename') {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['filename'] = $object->value;
                        }
                    }
                }

                if ($structure->ifparameters) {
                    foreach ($structure->parameters as $object) {
                        if (strtolower($object->attribute) == 'name') {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['name'] = $object->value;
                        }
                    }
                }

                if ($attachments[$i]['is_attachment']) {
                    $attachments[$i]['attachment'] = $imageData[$i];
                    /* 4 = QUOTED-PRINTABLE encoding */
                    if ($structure->encoding == 3) {
                        $attachments[$i]['attachment'] = $imageData[$i];
                    }
                    /* 3 = BASE64 encoding */ elseif ($structure->encoding == 4) {
                        $attachments[$i]['attachment'] = $imageData[$i];
                    }
                }
            }
            return $attachments;
        }
    }

    public function saveAttachment($attachments, $conversation_id) {
        $file_id = [];
        foreach ($attachments as $attachment) {
            if ($attachment['is_attachment'] == 1) {
                try {
                    $currentFile = $attachment['name'];
                    $ext = pathinfo($currentFile, PATHINFO_EXTENSION);
                    $fileName = md5(uniqid(rand(), true)) . "." . $ext;
                    $path = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                            ->getAbsolutePath("ticketsystem/attachment");
                    if (!is_dir($path)) {
                        mkdir($path, 0777, true);
                    }
                    if (empty($fileName))
                        $fileName = $attachment['filename'];
                    if (empty($fileName))
                        $fileName = time() . ".dat";
                    $fp = fopen($path . "/" . $fileName, "w+");
                    fwrite($fp, $attachment['attachment']);
                    fclose($fp);
                    $file = $this->_attachment->create();
                    $file->setData("conversation_id", $conversation_id);
                    $file->setData("file", $fileName);
                    $file->setData("current_file_name", $currentFile);
                    $file_id [] = $file->save()->getId();
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    return false;
                }
            }
        }
        return $file_id;
    }

    public function getMessage($emails, $storeId) {
        try {
            foreach ($emails as $email) {

                $subject = imap_utf8($email["header"]->subject);
                $reply = $replyEmail = $email["header"]->from;
                $replyEmail = $reply[0]->mailbox . "@" . $reply[0]->host;
                $customerName = $reply[0]->personal;
                $ticketId = $this->getIdFromSubject($subject);
                if (isset($email["structure"])) {
                    $structure = $email["structure"];
                }
                $first = explode(strip_tags($this->getSeprator()), $email["message"]);
                $message = $first[0];
                $lines = explode("\n", $message);
                while (count($lines) > 0 && (trim(end($lines)) == '' || trim(end($lines)) == '>')) {
                    array_pop($lines);
                }
                $lastline = end($lines);
                if ((substr($lastline, 0, 2) == 'On' || substr($lastline, 0, 2) == 'El') && substr($lastline, -1) == ':'
                ) {
                    array_pop($lines);
                }

                foreach ($lines as $key => $lastline) {
                    if ((preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{1,2}:[0-9]{2} GMT/', $lastline) || substr($lastline, 0, 2) == 'On' || substr($lastline, 0, 2) == 'El' || $lastline == '' || substr(trim($lastline), -1) == ':' || trim(substr($lastline, 0, 1)) == '>' || trim(substr($lastline, 0, 3)) == '>' || trim(substr($lastline, 0, 2)) == '>>')
                    ) {
                        unset($lines[$key]);
                    }
                }
                $message = trim(implode("\n", $lines));
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $allowNew = $this->scopeConfig->getValue("emipro/emipro_emailgateway/new_ticket", $storeScope);
                if (!$ticketId && !$allowNew) {
                    return false;
                }
                if ($ticketId) {
                    $this->saveTicketReply($ticketId, $message, $replyEmail, $email['attachment'], $storeId);
                } else {
                    $this->createNewTicket($subject, $message, $replyEmail, $customerName, $email['attachment'], $storeId);
                }
            }
            return true;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return false;
        }
    }

    public function createNewTicket($subject, $message, $customerEmail, $customerName, $attachment, $storeId) {
        $customerId = null;
        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $store = $storeId;
        $this->_appEmulation->startEnvironmentEmulation($store);
        $ticketStoreId = $this->scopeConfig->getValue("emipro/emipro_emailgateway/store", $storeScope);
        $departmentId = $this->scopeConfig->getValue("emipro/emipro_emailgateway/department", $storeScope);
        $priority = $this->scopeConfig->getValue("emipro/emipro_emailgateway/priority", $storeScope);
        $status = $this->scopeConfig->getValue("emipro/emipro_emailgateway/ticket_status", $storeScope);
        $allowGuest = $this->scopeConfig->getValue("emipro/general/allow_guest", $storeScope);
        $file_id = "";
        $externalId = md5(time() . $storeId . $customerEmail);
        $websiteId = $this->_store->getStore()->getWebsiteId();
        $customer = $object_manager->create('Magento\Customer\Model\Customer');
        $customer->setWebsiteId($websiteId)->loadByEmail($customerEmail);
        $customer_name = $customerName;
        if ($customer->getId()) {
            $customerName = $customer->getName();
            $customerId = $customer->getId();
        } else if (!$customer->getId() && !$allowGuest) {
            return false;
        }
        $admin_user_id = $this->scopeConfig->getValue('emipro/emipro_group/ticket_admin', $storeScope);
        $admin_email = $object_manager->create('Magento\User\Model\User')->load($admin_user_id)->getEmail();
        $ticket_email = $this->scopeConfig->getValue('emipro/emipro_group/ticket_email', $storeScope);
        $maxfile_size = 1024 * 1024 * 4; // 4 MB filesize;
        $deptModel = $this->_department->create()->load($departmentId);
        $currentDate = date('Y-m-d H:i', time());

        if ($message != "") {
            $model = $this->_ticketSystem->create(); //->setData($data);
            $model->setData("admin_user_id", $admin_user_id);
            $model->setData("customer_id", $customerId);
            $model->setData("customer_email", $customerEmail);
            $model->setData("customer_name", $customerName);
            $model->setData("external_id", $externalId);
            $model->setData("assign_admin_id", $deptModel->getAdminUserId());
            $model->setData("subject", $subject);
            $model->setData("priority_id", $priority);
            $model->setData("department_id", $departmentId);
            $model->setData("status_id", $status);
            $model->setData("date", $currentDate);
            $model->setData("unique_id", $this->getUniqueTicketCode());
            $model->setData("store_id", $ticketStoreId);
            try {
                $TicketId = $model->save()->getId();
                if ($TicketId != "") {
                    $con_model = $this->_conversation->create();
                    $con_model->setData("ticket_id", $TicketId);
                    $con_model->setData("message", $message);
                    $con_model->setData("name", $customerName);
                    $con_model->setData("status_id", $status);
                    $con_model->setData("date", $currentDate);
                    $con_model->save();
                    $con_id = $con_model->save()->getId();
                }

                if (count($attachment)) {
                    $file_id = $this->saveAttachment($attachment, $con_id);
                }
                $current_ticket_status = $this->_helper->getStatus($con_model->getStatusId());
                $customer_email = $customerEmail;
                $customer_name = $customerName;
                $ticket_template = $this->scopeConfig->getValue('emipro/template/ticket_create', $storeScope);
                $send_cc_email = $this->scopeConfig->getValue('emipro/emipro_group/superadmin_email', $storeScope);
                /*$sender_email = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/email', $storeScope);
                $sender_name = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/name', $storeScope);*/
                $sender_email = $this->scopeConfig->getValue('emipro/emipro_emailgateway/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $sender_name = $this->scopeConfig->getValue('emipro/emipro_emailgateway/owner', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $emailTemplateVariables = [];
                $subject = '[#' . $model->getUniqueId() . '-' . $TicketId . ']';
                if (!$customer->getId()) {
                    $guestUrl = $this->_helper->getGuestTicketUrl(["ticket_id" => $model->getExternalId(), "_secure" => true]);
                    $emailTemplateVariables["guest_url"] = $guestUrl;
                }

                $emailTemplateVariables['customer_name'] = $customer_name;
                $emailTemplateVariables['message'] = nl2br($con_model->getMessage());
                $emailTemplateVariables['ticket_id'] = $TicketId;
                $emailTemplateVariables['tempsubject'] = $subject . " New support ticket with ticket Id " . $TicketId;

                $emailTemplateVariables['sender_name'] = $sender_name;
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
                }
                $options = [
                    'area' => "frontend",
                    'store' => $storeId,
                ];
                $this->_helper->sendTicketMail($sender_name, $sender_email, $admin_email, $ticket_template, $options, $emailTemplateVariables, $cc);
                $options = [
                    'area' => "frontend",
                    'store' => $storeId,
                ];
                $emailTemplateVariables = [];
                $emailTemplateVariables['seprator'] = $this->getSeprator();
                $emailTemplateVariables['customer_name'] = $customer_name;
                $emailTemplateVariables['message'] = nl2br($model->getMessage());
                $emailTemplateVariables['ticket_id'] = $TicketId;
                $emailTemplateVariables['tempsubject'] = $subject . " Support ticket has been created with Ticket Id " . $TicketId;
                $template = $this->scopeConfig->getValue('emipro/template/ticket_create_customer', $storeScope);
                $this->_helper->sendTicketMail($sender_name, $sender_email, $customerEmail, $template, $options, $emailTemplateVariables);
                $this->_appEmulation->stopEnvironmentEmulation();
                return true;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_helper->log($e->getMessage());
            }
        }
    }

    public function saveTicketReply($ticketId, $message, $replyEmail, $attachment, $storeId) {
        $this->_appEmulation->startEnvironmentEmulation($storeId);
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $data["status"] = 4;
        $file_id = [];
        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($data != "") {

            try {
                $ticketSystem = $this->_ticketSystem->create()->load($ticketId);
                $customerId = $ticketSystem->getCustomerId();
                if ($customerId) {
                    $customer = $object_manager->get('\Magento\Customer\Model\Customer')->load($customerId);
                    $customerEmail = $customer->getEmail();
                    $customerName = $customer->getName();
                } else {
                    $customerEmail = $ticketSystem->getCustomerEmail();
                    $customerName = $ticketSystem->getCustomerName();
                }
                $customer = $object_manager->get('\Magento\Customer\Model\Customer')->load($customerId);
                if ($customerEmail == $replyEmail) {
                    $adminId = $ticketSystem->getAssignAdminId();
                    $assign_admin = $object_manager->create('Magento\User\Model\User')->load($adminId);
                    $assign_admin_email = $assign_admin->getEmail();
                    $assing_admin_name = $assign_admin->getName();
                    $ticket_email = $this->scopeConfig->getValue('emipro/emipro_group/ticket_email', $storeScope);
                    $ticket_email = $this->scopeConfig->getValue('emipro/emipro_group/ticket_email', $storeScope);
                    /*$ticket_admin_name = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/name', $storeScope);*/
                    $ticket_admin_name = $this->scopeConfig->getValue('emipro/emipro_emailgateway/owner', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $data["name"] = $customerName;
                    $data["date"] = date('Y-m-d H:i', time());
                    ;
                    $current_status = $data["status"];
                    $status = ["status_id" => $current_status, "assign_admin_id" => $adminId, "lastupdated_date" => $data["date"]];

                    $ticketSystem->addData($status);
                    $ticketSystem->setId($ticketId)->save();

                    $model = $this->_conversation->create();
                    $model->setData("ticket_id", $ticketId);
                    $model->setData("message", $message);
                    $model->setData("status_id", $current_status);
                    $model->setData("name", $customerName);
                    $model->setData("date", $data['date']);
                    $con_id = $model->save()->getId();
                    $current_ticket_status = $this->_helper->getStatus($model->getStatusId());
                    if (count($attachment)) {
                        $file_id = $this->saveAttachment($attachment, $con_id);
                    }
                    // ticket conversation mail send to customer.
                    $TicketId = $ticketId;
                    $customer_email = $customer->getEmail();
                    $customer_name = $customer->getFirstname();
                    $ticket_template = $this->scopeConfig->getValue('emipro/template/ticket_conversation', $storeScope);
                    /*$sender_email = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/email', $storeScope);*/
                    $sender_email = $this->scopeConfig->getValue('emipro/emipro_emailgateway/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $emailTemplateVariables = [];
                    $emailTemplateVariables['customer_name'] = $assing_admin_name;
                    $emailTemplateVariables['message'] = nl2br($model->getMessage());
                    $emailTemplateVariables['ticket_id'] = $ticketId;
                    $emailTemplateVariables['sender_name'] = $customer_name;
                    $emailTemplateVariables['ticket_status'] = $current_ticket_status;
                    $options = [
                        'area' => "frontend",
                        'store' => $storeId,
                    ];
                    $emailTemplateVariables['customer_name'] = $customer_name;
                    $emailTemplateVariables['tempsubject'] = "[#" . $ticketSystem->getUniqueId() . "-" . $ticketId . "]Reply for support ticket " . $ticketId;
                    /*$sender_name = $this->scopeConfig->getValue('trans_email/ident_' . $ticket_email . '/name', $storeScope);*/
                    $sender_name = $this->scopeConfig->getValue('emipro/emipro_emailgateway/owner', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    
                    $this->_helper->sendTicketMail($sender_name, $sender_email, $assign_admin_email, $ticket_template, $options, $emailTemplateVariables);
                    $this->_appEmulation->stopEnvironmentEmulation();
                    return true;
                } else {
                    $this->_helper->log(__("Customer not valid"));
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_helper->log($e->getMessage());
                return false;
            }
        }
    }

}
