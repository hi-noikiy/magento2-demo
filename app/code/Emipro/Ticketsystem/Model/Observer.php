<?php

namespace Emipro\Ticketsystem\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Emipro\Ticketsystem\Model\TicketSystemFactory;
use Emipro\Ticketsystem\Model\TicketConversationFactory;

class Observer {

    
    protected $_ticket;
    protected $scopeConfig;
    protected $_conversation;

    public function __construct(
    ScopeConfigInterface $scopeConfig, TicketSystemFactory $ticketFactory, TicketConversationFactory $ticketConversationFactory
    ) {

        $this->scopeConfig = $scopeConfig;
        $this->_ticket = $ticketFactory;
        $this->_conversation = $ticketConversationFactory;
       
    }

    public function changeStatus() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeStore = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $auto_status = $this->scopeConfig->getValue('emipro/general/enabled', $scopeStore);
        if ($auto_status == 1) {
            $ticketSystem = $this->_ticket->create()->getCollection();
            $days = $this->scopeConfig->getValue('emipro/general/days', $scopeStore);
            $currentDate = date('Y-m-d', time());
            $t = $this->_ticket->create()->getCollection()->getData();
            foreach ($ticketSystem as $ticket) {
                $conversation_info = $this->_conversation->create()->getCollection()->addFieldToFilter("ticket_id", $ticket->getTicketId())->setOrder("conversation_id", "DESC");
                foreach ($conversation_info as $value) {
                    $lastupdated_date = substr($value["date"], 0, 10);
                    $date = date('Y-m-d', strtotime("+" . $days . "day", strtotime($lastupdated_date)));
                    if ($date == $currentDate) {
                    $test = $objectManager->create("Emipro\Ticketsystem\Model\TicketSystem");
                        $Model = $test->load($ticket->getTicketId());
                        if ($Model->getStatusId() == 5) {
                            $status = ["status_id" => 2];
                            $Model->addData($status);
                            $Model->setId($ticket->getTicketId())->save();
                        }
                    }
                }
            }
        }
    }
}