<?php

namespace Emipro\Ticketsystem\Model\ResourceModel\TicketConversation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection {

    protected function _construct() {
        $this->_init(
                'Emipro\Ticketsystem\Model\TicketConversation', 'Emipro\Ticketsystem\Model\ResourceModel\TicketConversation'
        );
    }

}
