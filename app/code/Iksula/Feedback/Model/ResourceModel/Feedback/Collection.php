<?php

namespace Iksula\Feedback\Model\ResourceModel\Feedback;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Feedback\Model\Feedback', 'Iksula\Feedback\Model\ResourceModel\Feedback');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>