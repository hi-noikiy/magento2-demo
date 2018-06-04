<?php

namespace Iksula\Orderreturns\Model\ResourceModel\Orderreturn;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Orderreturns\Model\Orderreturn', 'Iksula\Orderreturns\Model\ResourceModel\Orderreturn');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>