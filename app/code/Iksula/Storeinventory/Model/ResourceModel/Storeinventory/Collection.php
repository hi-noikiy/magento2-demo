<?php

namespace Iksula\Storeinventory\Model\ResourceModel\Storeinventory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Storeinventory\Model\Storeinventory', 'Iksula\Storeinventory\Model\ResourceModel\Storeinventory');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>