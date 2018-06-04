<?php

namespace Emipro\Ticketsystem\Model\ResourceModel\TicketSystem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection {

    protected function _construct() {
        $this->_init(
                'Emipro\Ticketsystem\Model\TicketSystem', 'Emipro\Ticketsystem\Model\ResourceModel\TicketSystem'
        );
    }
    public function getAllTickets($customerId)
    {

		$collection=$this->getSelect()
                    ->join(array('status' => $this->getTable('emipro_ticket_status')), 'main_table.status_id=status.status_id', 'status')
                    ->join(array('priority' => $this->getTable('emipro_ticket_priority')), 'main_table.priority_id=priority.priority_id', 'priority')
                    ->where('main_table.customer_id=' . $customerId)
                    ->order('main_table.ticket_id DESC');

                    return $collection;
	}
    public function getSelectCountSql() {
        $countSelect = parent::getSelectCountSql();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);

        if (count($this->getSelect()->getPart(\Magento\Framework\DB\Select::GROUP)) > 0) {
            $countSelect->reset(\Magento\Framework\DB\Select::GROUP);
            $countSelect->distinct(true);
            $group = $this->getSelect()->getPart(\Magento\Framework\DB\Select::GROUP);
            $countSelect->columns("COUNT(DISTINCT " . implode(", ", $group) . ")");
        } else {
            $countSelect->columns('COUNT(*)');
        }

        return $countSelect;
    }

}
