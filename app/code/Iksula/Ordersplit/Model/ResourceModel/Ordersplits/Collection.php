<?php

namespace Iksula\Ordersplit\Model\ResourceModel\Ordersplits;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Ordersplit\Model\Ordersplits', 'Iksula\Ordersplit\Model\ResourceModel\Ordersplits');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>