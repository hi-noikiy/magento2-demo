<?php
namespace Iksula\Orderreturns\Model\ResourceModel;

class Orderreturn extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('custom_order_returns', 'id');
    }
}
?>