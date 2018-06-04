<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Ticket;

use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class View extends FormContainer {

    public function __construct(
    \Magento\Backend\Block\Widget\Context $context, \Magento\Framework\Registry $registry, array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct() {
        $this->_objectId = 'ticket_id';
        $this->_mode = "view";
        $this->_blockGroup = 'emipro_ticketsystem';
        $this->_controller = 'adminhtml_ticket';
        parent::_construct();
        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
        $this->buttonList->update('delete', 'label', __('Delete Ticket'));
    }

}
