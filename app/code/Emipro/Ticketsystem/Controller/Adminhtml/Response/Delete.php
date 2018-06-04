<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Response;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Emipro\Ticketsystem\Model\TicketresponseFactory;

class Delete extends Action {

    protected $_ticketresponse;

    public function __construct(
    Context $context, TicketresponseFactory $ticketresponseFactory
    ) {
        $this->_ticketresponse = $ticketresponseFactory;
        parent::__construct($context);
    }

    public function execute() {
        $delete = 0;
        $resultRedirect = $this->resultRedirectFactory->create();
        $deleteId = $this->getRequest()->getParam("response_id");
        $ticketResponse = $this->_ticketresponse->create();
        try {
            if ($deleteId) {
                    $ticketResponse->load($deleteId);
                    $ticketResponse->delete();


                $this->messageManager->addSuccess(__('Frequent Response has been deleted.', $delete));
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
