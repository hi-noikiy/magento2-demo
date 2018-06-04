<?php
namespace Iksula\Ordersplit\Model\ResourceModel;

class Rejection extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rejection_history_tb', 'id');
    }
}
?>