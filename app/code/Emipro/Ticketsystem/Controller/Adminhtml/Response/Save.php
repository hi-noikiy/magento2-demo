<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Response;

use Emipro\Ticketsystem\Model\TicketresponseFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

class Save extends Action {

    protected $_ticketresponse;

    public function __construct(
    Context $context, Registry $registry, TicketresponseFactory $ticketresponseFactory
    ) {
        $this->_ticketresponse = $ticketresponseFactory;
        parent::__construct($context);
    }

    public function execute() {
        $data = $this->getRequest()->getPost("response");
		$id=$this->getRequest()->getParam("response_id");
        $resultRedirect = $this->resultRedirectFactory->create();
        $back=$this->getRequest()->getParam("back");
        $response = $this->_ticketresponse->create();
        if ($id) {
            $response->load($data["response_id"]);
        }
        $response->setData($data);
        try {
            $response->save();
            $this->messageManager->addSuccess(__('Response has been saved.'));
            if(isset($back))
			{
				$resultRedirect->setPath('emipro_ticketsystem/*/edit', ['response_id' => $response->getId(), '_current' => true]);
			}
			else{
				$resultRedirect->setPath('emipro_ticketsystem/*/');
			}
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            //echo $e->getMessage();
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $resultRedirect;
    }
}
