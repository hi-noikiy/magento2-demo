<?php

namespace Emipro\Ticketsystem\Model\ResourceModel\Ticketdepartment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection {

    protected function _construct() {
        $this->_init(
                'Emipro\Ticketsystem\Model\Ticketdepartment', 'Emipro\Ticketsystem\Model\ResourceModel\Ticketdepartment'
        );
    }

}
