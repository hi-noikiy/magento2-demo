<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Emipro\Ticketsystem\Model\TicketSystemFactory;

class MassDelete extends Action {

    protected $_ticket;
    protected $_resultRedirectFactory;

    public function __construct(
    Context $context, TicketSystemFactory $TicketSystemFactory
    ) {
        $this->_ticket = $TicketSystemFactory;
        parent::__construct($context);
    }

    public function execute() {
        $delete = 0;
        $resultRedirect = $this->resultRedirectFactory->create();
        $deleteIds = $this->getRequest()->getPost("ticket_id");
        $ticket = $this->_ticket->create();
        try {
            if ($deleteIds) {
                foreach ($deleteIds as $id) {
                    $ticket->load($id);
                    $ticket->delete();
                    $delete++;
                }
                $this->messageManager->addSuccess(__('Total %1 Ticket has been deleted.', $delete));
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
