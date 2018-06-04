<?php

namespace Iksula\Orderitemstatus\Model\ResourceModel\Orderitemstatus;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Orderitemstatus\Model\Orderitemstatus', 'Iksula\Orderitemstatus\Model\ResourceModel\Orderitemstatus');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>