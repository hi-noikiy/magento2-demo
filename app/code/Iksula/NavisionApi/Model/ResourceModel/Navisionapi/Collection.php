<?php

namespace Iksula\NavisionApi\Model\ResourceModel\Navisionapi;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\NavisionApi\Model\Navisionapi', 'Iksula\NavisionApi\Model\ResourceModel\Navisionapi');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>