<?php
namespace Iksula\Storeinventory\Model\ResourceModel;

class Storeinventory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('storeinventory_tb', 'id');
    }
}
?>