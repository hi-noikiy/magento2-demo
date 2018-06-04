<?php

namespace Iksula\ColorSwatch\Model\ResourceModel\Productdetailpg;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\ColorSwatch\Model\Productdetailpg', 'Iksula\ColorSwatch\Model\ResourceModel\Productdetailpg');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>