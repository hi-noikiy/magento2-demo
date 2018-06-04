<?php
namespace Iksula\Report\Model\ResourceModel;

class Reports extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ordersplit_dup', 'ordersplit_dup_id');
    }
}
?>