<?php

namespace Emipro\Ticketsystem\Helper;

use Emipro\Ticketsystem\Model\TicketdepartmentFactory;
use Emipro\Ticketsystem\Model\TicketPriorityFactory;
use Emipro\Ticketsystem\Model\TicketStatusFactory;
use Emipro\Ticketsystem\Model\TicketSystemFactory;
use Emipro\Ticketsystem\Model\TicketresponseFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Url;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Customer\Model\CustomerFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_department;
    protected $_status;
    protected $_priority;
    protected $_logger;
    protected $_ticketSystem;
    protected $_filesystem;
    protected $_fileUploaderFactory;
    protected $_transport;
    protected $customerSession;
    protected $_customer;
    protected $_appEmulation;
    protected $_responseFactory;
    protected $_storeManager;
    protected $messageManager;
    protected $OrderCollectionFactory;
    protected $_ticketresponse;

    public function __construct(
    \Magento\Framework\App\Helper\Context $context, 
    TicketdepartmentFactory $TicketdepartmentFactory, 
    TicketPriorityFactory $TicketPriorityFactory, 
    TicketStatusFactory $TicketStatusFactory, 
    TicketSystemFactory $ticketFactory,
    TicketresponseFactory $TicketresponseFactory,  
    Filesystem $filesystem, 
    CustomerFactory $customer, 
    Url $url, 
    UploaderFactory $fileUploaderFactory, 
    TransportBuilder $transportBuilder, 
    \Magento\Store\Model\App\Emulation $appEmulation,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Framework\App\ResponseFactory $responseFactory, 
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Framework\Message\ManagerInterface $messageManager,
    \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $OrderCollectionFactory)
     {
        $this->_department = $TicketdepartmentFactory;
        $this->_status = $TicketStatusFactory;
        $this->_priority = $TicketPriorityFactory;
        $this->_ticketSystem = $ticketFactory;
        $this->_logger = $context->getLogger();
        $this->_transport = $transportBuilder;
        $this->url = $url;
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_customer = $customer;
        $this->_appEmulation = $appEmulation;
        $this->customerSession = $customerSession;
        $this->_storeManager=$storeManager;
        $this->messageManager=$messageManager;
        $this->_responseFactory=$responseFactory;
        $this->_orderCollectionFactory = $OrderCollectionFactory;
        $this->_ticketresponse=$TicketresponseFactory;
        parent::__construct($context);
    }

    public function saveAttachment($filename, $new_fileName, $directory) {
        try {
            $uploader = $this->_fileUploaderFactory->create(['fileId' => $filename]);
            $path = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                    ->getAbsolutePath($directory);
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            $uploader->save($path, $new_fileName);
            return true;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return false;
        }
    }

    public function getGuestTicketUrl($param) {
        return $this->url->getUrl('ticketsystem/index/viewguest/', $param);
    }

    public function getCustomerName($customer_id) {
        $customer = $this->_customer->create()->load($customer_id);
        $first_name = $customer->getFirstname();
        $last_name = $customer->getLastname();
        return $first_name . "  " . $last_name;
    }

    public function getCustomerStore($customer) {
        $customer = $customer = $this->_customer->create()->load($customer);
        return $customer->getStore();
    }

    public function validExternalId($id) {
        if ($id) {
            $ticketSystem = $this->_ticketSystem->create()->load($id, "external_id");
            if ($ticketSystem->getId()) {
                return true;
            }
            return false;
        }
    }

    public function validUser($id = null) {
        if ($id) {
            $customerId = $this->customerSession->getId();
            $ticketSystem = $this->_ticketSystem->create()->getCollection();
            $ticketSystem->addFieldToFilter("ticket_id", $id)
                    ->addFieldToFilter("customer_id", $customerId);
            if ($ticketSystem->getFirstItem()->getId()) {
                return true;
            }
            return false;
        }
        if ($this->customerSession->isLoggedIn()) {
            return true;
        }
        return false;
    }

    public function sendTicketMail($sender_name, $sender_email, $customer_email, $template, $options, $variables, $cc = null) {
       try{
        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData($variables);
        $from = ["name" => $sender_name, "email" => $sender_email];
        $transportTemplate = $this->_transport->setTemplateIdentifier($template)
                ->setTemplateOptions($options)
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($from)
                ->addTo($customer_email);
        if ($cc) {
            if (is_array($cc)) {
                foreach ($cc as $ccemail) {
                    $transportTemplate->addCc($ccemail);
                }
            }
            if (is_string($cc)) {
                $transportTemplate->addCc($cc);
            }
        }
        $transport = $transportTemplate->getTransport();
        $transport->sendMessage();
		}
		catch (\Magento\Framework\Exception\LocalizedException $e)
		{
			return false;
		}
	
    }

    public function getNextStatusForCustomer($current_status) {
        $Next_status = "";
        /* Cash sequence:
         * "New","Closed","Reopen","Waiting For Support","Waiting For Customer" */
        switch ($current_status) {
            case 1:
                return $Next_status = "4,5,2";
                break;
            case 2:
                return $Next_status = "3";
                break;
            case 3:
                return $Next_status = "4,5,2";
                break;
            case 4:
                return $Next_status = "4,5,2";
                break;
            case 5:
                return $Next_status = "4,5,2";
                break;
            default:
                return false;
        }
    }

    public function log($value) {
        $this->_logger->addDebug($value);
    }

    public function getLastupdatedDate($ticketId) {
        $conversation_info = $this->_status->create();
        $conversation_info->getCollection()->addFieldToFilter("ticket_id", $ticketId)->setOrder("conversation_id", "DESC");
        foreach ($conversation_info as $value) {
            return $value["date"];
        }
    }

    public function getStatus($status_id) {
        $model = $this->_status->create()->load($status_id);
        return $model->getStatus();
    }

    public function getStatusOptionsForAdmin($current_status) {
        $statusOptions = explode(",", $this->getNextStatusForAdmin($current_status));
        $selected = current($statusOptions);
        $ticket_status = $this->_status->create()->getCollection()->addFieldToFilter('status_id', array('in' => $statusOptions));
        $option = "";
        foreach ($ticket_status as $status) {
            if ($status["status_id"] == $selected) {
                $option.= "<option value=" . $status["status_id"] . " Selected >" . __($status["status"]) . "</option>";
            } else {
                $option.= "<option value=" . $status["status_id"] . " >" . __($status["status"]) . "</option>";
            }
        }
        return $option;
    }

    public function getStatusOptionsForCustomer($current_status) {
        $statusOptions = explode(",", $this->getNextStatusForCustomer($current_status));
        $selected = current($statusOptions);
        $ticket_status = $this->_status->create()->getCollection()->addFieldToFilter('status_id', array('in' => $statusOptions));
        $option = "";
        foreach ($ticket_status as $status) {
            if ($status["status_id"] == $selected) {
                $option.= "<option value=" . $status["status_id"] . " Selected >" . __($status["status"]) . "</option>";
            } else {
                $option.= "<option value=" . $status["status_id"] . " >" . __($status["status"]) . "</option>";
            }
        }
        return $option;
    }

    public function getCurrentAdminName() {
        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $user = $object_manager->get('\Magento\Backend\Model\Auth\Session');
        $userFirstname = $user->getUser()->getFirstname();
        $userLastname = $user->getUser()->getLastname();
        return $userFirstname . " " . $userLastname;
    }

    public function getNextStatusForAdmin($current_status) {

        $Next_status = "";
        /* "New","Closed","Reopen","Waiting For Support","Waiting For Customer" */
        switch ($current_status) {
            case 1:
                return $Next_status = "5,4,2";
                break;
            case 2:
                return $Next_status = "3";
                break;
            case 3:
                return $Next_status = "5,4,2";
                break;
            case 4:
                return $Next_status = "5,4,2";
                break;
            case 5:
                return $Next_status = "5,4,2";
                break;
            default:
                return false;
        }
    }

    public function getAdminUser() {
        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $adminUserModel = $object_manager->get('Magento\User\Model\User')->getCollection();
        $option = [];
        $adminUser = $adminUserModel->getData();
        foreach ($adminUser as $value) {
            $option[$value["user_id"]] = $value["firstname"] . " " . $value["lastname"];
        }
        return $option;
    }

    public function getTicketdept() {
        $option = [];
        $ticketDepartment = $this->_department->create()->getCollection()->getData();
        foreach ($ticketDepartment as $dept) {
            if ($dept["status"] != 0) {
                $option[$dept["department_id"]] = __($dept["department_name"]);
            }
        }
        return $option;
    }

    public function getConfig($config_path,$store=0) {
		if($store)
		{
			$this->_appEmulation->startEnvironmentEmulation($store);
		}
        $value=$this->scopeConfig->getValue(
                        $config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if($store)
        {
         $this->_appEmulation->stopEnvironmentEmulation();
	 }
        return $value;
    }

    public function getTicketpriority() {
        $option = [];
        $ticketPriority = $this->_priority->create()->getCollection()->getData();
        foreach ($ticketPriority as $priority) {
            $option[$priority["priority_id"]] = __($priority["priority"]);
        }
        return $option;
    }

    public function getTicketstatus() {
        $option = [];
        $ticketStatus = $this->_status->create()->getCollection()->getData();
        foreach ($ticketStatus as $status) {
            $option[$status["status_id"]] = $status["status"];
        }
        return $option;
    }
    
    /* start by ept 68 */
    public function getCustomerOrderIds($customerId){
		$option= [];
		$orderIds = $this->_orderCollectionFactory->create()->addFieldToFilter('customer_id',$customerId)->addFieldToSelect('increment_id');
		foreach($orderIds as $orderid){
			$option[$orderid['increment_id']]=$orderid['increment_id'];
		}
		return $option;
	}
	public function getResponseTitle(){
		$option = [];
        $ticketResponse = $this->_ticketresponse->create()->getCollection()->addFieldToFilter('status',1)->getData();
        foreach ($ticketResponse as $response) {
            $option[$response["response_id"]]=$response["response_title"];
        }
        return $option;
	}
	public function getCurrentStoreId(){
		return $this->_storeManager->getStore()->getId();
	}
	public function getstores(){
		$options=[];
		$stores=$this->_storeManager->getStores($withDefault = false);
		foreach($stores as $store){
		$options[$store->getId()]=$store->getName();
	}
		return $options;
	}
	public  function getOrderUrl($increment_id) {
		$orderId = $this->_orderCollectionFactory->create()->addFieldToFilter('increment_id',$increment_id)
		->addFieldToSelect('entity_id')->getData();
		if($orderId){
			return $orderId[0]['entity_id'];
		}else {
			return;
		}
		
	}
    /*end by ept 68 */

}
