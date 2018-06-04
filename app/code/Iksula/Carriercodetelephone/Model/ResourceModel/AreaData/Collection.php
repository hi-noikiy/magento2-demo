<?php

namespace Iksula\Carriercodetelephone\Model\ResourceModel\AreaData;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Carriercodetelephone\Model\AreaData', 'Iksula\Carriercodetelephone\Model\ResourceModel\AreaData');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>