<?php

namespace Emipro\Ticketsystem\Block;

use Emipro\Ticketsystem\Model\TicketSystemFactory;
use Magento\Framework\View\Element\Template;
use Magento\Backend\Model\View\Result\RedirectFactory;

class TicketView extends Template {

    protected $_ticket;
    protected $customerSession;
    protected $redirect;
    protected $messageManager;

    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Message\ManagerInterface $messageManager, RedirectFactory $redirectFactory, TicketSystemFactory $ticketFactory
    ) {
        $this->_ticket = $ticketFactory;
        $this->customerSession = $customerSession;
        $this->redirect = $redirectFactory;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    public function getTicket() {
        $customerId = $this->customerSession->getId();
        $isguest = $this->getRequest()->getActionName();
        $id = $this->getRequest()->getParam("id");
        $ticketCollection = $this->_ticket->create()->getCollection();
        $ticketCollection->getSelect()
                ->join(array('status' => $ticketCollection->getTable('emipro_ticket_status')), 'main_table.status_id=status.status_id', 'status')
                ->join(array('priority' => $ticketCollection->getTable('emipro_ticket_priority')), 'main_table.priority_id=priority.priority_id', 'priority')
                ->join(array('department' => $ticketCollection->getTable('emipro_ticket_department')), 'main_table.department_id=department.department_id', 'department_name');
        if ($isguest == "viewguest") {
            $id = $this->getRequest()->getParam("ticket_id");
            $ticketCollection->getSelect()->where("main_table.external_id='$id'");
        } else {
            $ticketCollection->getSelect()->where("main_table.ticket_id=$id and main_table.customer_id=$customerId");
        }

        $ticketData = $ticketCollection->getFirstItem();
        if ($ticketData->getId()) {
            return $ticketData;
        }
        $this->messageManager->addError(__("You don't have permission to view this ticket"));
        return $this->redirect->create()->setPath('*/*/');
    }

    public function getBackUrl() {
        return $this->getUrl("ticketsystem/*/tickethistory", ['_secure' => true]);
    }

}
