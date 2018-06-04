<?php

namespace Iksula\Fetchrapi\Model\ResourceModel\Fetchr;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Fetchrapi\Model\Fetchr', 'Iksula\Fetchrapi\Model\ResourceModel\Fetchr');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>