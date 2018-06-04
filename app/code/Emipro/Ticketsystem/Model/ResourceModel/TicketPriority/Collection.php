<?php

namespace Emipro\Ticketsystem\Model\ResourceModel\TicketPriority;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection {

    protected function _construct() {
        $this->_init(
                'Emipro\Ticketsystem\Model\TicketPriority', 'Emipro\Ticketsystem\Model\ResourceModel\TicketPriority'
        );
    }

}
