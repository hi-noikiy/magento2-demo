<?php
namespace Iksula\Ordersplit\Model;

class Rejection extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Ordersplit\Model\ResourceModel\Rejection');
    }
}
?>