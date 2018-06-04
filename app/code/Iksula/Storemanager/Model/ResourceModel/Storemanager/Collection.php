<?php

namespace Iksula\Storemanager\Model\ResourceModel\Storemanager;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Storemanager\Model\Storemanager', 'Iksula\Storemanager\Model\ResourceModel\Storemanager');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>