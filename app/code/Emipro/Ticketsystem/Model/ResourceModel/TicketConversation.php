<?php

namespace Emipro\Ticketsystem\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TicketConversation extends AbstractDb {

    protected function _construct() {
        $this->_init("emipro_ticket_conversation", "conversation_id");
    }

}
