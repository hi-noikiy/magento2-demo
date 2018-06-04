<?php

namespace Emipro\Ticketsystem\Controller\Adminhtml\Cron;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Emipro\Ticketsystem\Model\Mails;

class Index extends Action {

    protected $_emailFactory;
    protected $_resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory, Mails $emailFactory) {

        parent::__construct($context);
        $this->_emailFactory = $emailFactory;
        $this->_resultPageFactory = $resultPageFactory;
    }

    public function execute() {
        $this->_emailFactory->fetchMails();
    }

}
