<?php

namespace Emipro\Ticketsystem\Model\ResourceModel\Ticketresponse;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection {

    protected function _construct() {
        $this->_init(
                'Emipro\Ticketsystem\Model\Ticketresponse', 'Emipro\Ticketsystem\Model\ResourceModel\Ticketresponse'
        );
    }

}
