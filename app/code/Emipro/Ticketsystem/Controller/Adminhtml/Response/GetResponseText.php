<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Response;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Emipro\Ticketsystem\Model\TicketresponseFactory;
use Emipro\Ticketsystem\Helper\Data;
use Magento\Framework\Controller\ResultFactory;

class GetResponseText extends Action {
	
	protected $resultPageFactory;
    protected $_helper;
    protected $_ticketresponse;
    protected $request;
    public function __construct(
    Context $context, PageFactory $resultPageFactory, TicketresponseFactory $TicketresponseFactory,Data $helper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_ticketresponse=$TicketresponseFactory;
        $this->_helper=$helper;
    }

    public function execute() {
		$id=$this->getRequest()->getPost("responseid");
		$data = $this->_ticketresponse->create()->load($id)->getResponseText();
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($data);
        return $resultJson;
	}
	
}

