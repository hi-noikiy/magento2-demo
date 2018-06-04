<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Response;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Emipro\Ticketsystem\Model\TicketresponseFactory;

class MassDelete extends Action {

    protected $_ticketresponse;
    protected $_resultRedirectFactory;

    public function __construct(
    Context $context, TicketresponseFactory $ticketresponseFactory
    ) {
        $this->_ticketresponse = $ticketresponseFactory;
        parent::__construct($context);
    }

    public function execute() {
        $delete = 0;
        $resultRedirect = $this->resultRedirectFactory->create();
        $deleteIds = $this->getRequest()->getPost("response_id");
        $ticketresponse = $this->_ticketresponse->create();
        try {
            if ($deleteIds) {
                foreach ($deleteIds as $id) {
                    $ticketresponse->load($id);
                    $ticketresponse->delete();
                    $delete++;
                }
                $this->messageManager->addSuccess(__('Total %1 Frequest Response has been deleted.', $delete));
                $resultRedirect->setPath('emipro_ticketsystem/*/', ['_current' => true]);
                return $resultRedirect;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addSuccess(__('Can not delete Frequent Response.'));
            $resultRedirect->setPath('emipro_ticketsystem/*/', ['_current' => true]);
            return $resultRedirect;
        }
    }

}
