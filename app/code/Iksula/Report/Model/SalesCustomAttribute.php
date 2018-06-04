<?php
namespace Iksula\Report\Model;

class SalesCustomAttribute extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Report\Model\ResourceModel\SalesCustomAttribute');
    }
}
?>