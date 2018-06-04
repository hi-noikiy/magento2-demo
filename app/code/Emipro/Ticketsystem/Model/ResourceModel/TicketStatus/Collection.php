<?php

namespace Emipro\Ticketsystem\Model\ResourceModel\TicketStatus;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection {

    protected function _construct() {
        $this->_init(
                'Emipro\Ticketsystem\Model\TicketStatus', 'Emipro\Ticketsystem\Model\ResourceModel\TicketStatus'
        );
    }

}
