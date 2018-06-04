<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Emipro\Ticketsystem\Model\TicketSystemFactory;

class Delete extends Action {

    protected $_ticket;
    protected $_resultRedirectFactory;

    public function __construct(
    Context $context, TicketSystemFactory $TicketSystemFactory
    ) {
        $this->_ticket = $TicketSystemFactory;
        parent::__construct($context);
    }

    public function execute() {

        $resultRedirect = $this->resultRedirectFactory->create();
        $deleteId = $this->getRequest()->getParam("ticket_id");
        $ticket = $this->_ticket->create();
        try {
            if ($deleteId) {

                $ticket->load($deleteId);
                $ticket->delete();


                $this->messageManager->addSuccess(__('Ticket has been deleted.'));
                $resultRedirect->setPath('emipro_ticketsystem/*/', ['_current' => true]);
                return $resultRedirect;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addSuccess(__('Can not delete ticket.'));
            $resultRedirect->setPath('emipro_ticketsystem/*/', ['_current' => true]);
            return $resultRedirect;
        }
    }

}
