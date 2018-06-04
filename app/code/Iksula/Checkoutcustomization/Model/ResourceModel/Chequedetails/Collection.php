<?php

namespace Iksula\Checkoutcustomization\Model\ResourceModel\Chequedetails;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Checkoutcustomization\Model\Chequedetails', 'Iksula\Checkoutcustomization\Model\ResourceModel\Chequedetails');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>