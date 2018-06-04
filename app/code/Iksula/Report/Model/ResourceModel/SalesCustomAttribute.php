<?php
namespace Iksula\Report\Model\ResourceModel;

class SalesCustomAttribute extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_custom_attributes', 'custom_id');
    }
}
?>