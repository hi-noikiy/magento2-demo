<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Ticket\View\Tab;

use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Context;

class Ticket extends \Magento\Backend\Block\Widget {

    protected $_template = 'ticket/ticket.phtml';
    protected $_coreRegistry;
    protected $_helper;

    public function __construct(Context $context, Registry $registry, array $data = []) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function getTicket() {
        return $this->_coreRegistry->registry("ticket");
    }

}
