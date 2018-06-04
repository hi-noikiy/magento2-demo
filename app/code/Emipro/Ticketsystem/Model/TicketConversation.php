<?php

namespace Emipro\Ticketsystem\Model;

use Magento\Framework\Model\AbstractModel;

class TicketConversation extends AbstractModel {

    protected function _construct() {
        $this->_init('Emipro\Ticketsystem\Model\ResourceModel\TicketConversation');
    }

}
