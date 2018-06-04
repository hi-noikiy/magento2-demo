<?php

namespace Iksula\Ordersplit\Model\ResourceModel\Rejection;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Ordersplit\Model\Rejection', 'Iksula\Ordersplit\Model\ResourceModel\Rejection');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>