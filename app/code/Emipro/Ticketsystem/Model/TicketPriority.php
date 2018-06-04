<?php

namespace Emipro\Ticketsystem\Model;

use Magento\Framework\Model\AbstractModel;

class TicketPriority extends AbstractModel {

    protected function _construct() {
        $this->_init('Emipro\Ticketsystem\Model\ResourceModel\TicketPriority');
    }

}
