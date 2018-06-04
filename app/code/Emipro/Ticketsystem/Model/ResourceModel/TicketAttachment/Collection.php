<?php

namespace Emipro\Ticketsystem\Model\ResourceModel\TicketAttachment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection {

    protected function _construct() {
        $this->_init(
                'Emipro\Ticketsystem\Model\TicketAttachment', 'Emipro\Ticketsystem\Model\ResourceModel\TicketAttachment'
        );
    }

}
