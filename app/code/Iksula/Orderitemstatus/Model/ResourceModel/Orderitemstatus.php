<?php
namespace Iksula\Orderitemstatus\Model\ResourceModel;

class Orderitemstatus extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('orderitemstatus_tb', 'id');
    }
}
?>