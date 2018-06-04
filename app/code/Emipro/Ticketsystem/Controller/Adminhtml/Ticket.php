<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Emipro\Ticketsystem\Model\TicketSystemFactory;

abstract class Ticket extends Action {

    protected $_ticket;

    public function __construct(
    Context $context, TicketSystemFactory $ticketFactory
    ) {
        $this->_ticket = $ticketFactory;
        parent::__construct($context);
    }

}
