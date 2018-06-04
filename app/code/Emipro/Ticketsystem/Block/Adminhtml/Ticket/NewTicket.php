<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Ticket;

use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class NewTicket extends FormContainer {

    protected function _construct() {
        $this->_objectId = 'newticket_id';
        $this->_blockGroup = 'emipro_ticketsystem';
        $this->_controller = 'adminhtml_Ticket';
        $this->_mode = 'newticket';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Ticket'));
    }

}
