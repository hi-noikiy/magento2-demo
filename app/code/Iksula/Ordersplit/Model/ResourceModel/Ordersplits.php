<?php
namespace Iksula\Ordersplit\Model\ResourceModel;

class Ordersplits extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ordersplits_tb', 'id');
    }
}
?>