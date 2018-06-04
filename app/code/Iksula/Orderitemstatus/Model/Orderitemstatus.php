<?php
namespace Iksula\Orderitemstatus\Model;

class Orderitemstatus extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Orderitemstatus\Model\ResourceModel\Orderitemstatus');
    }
}
?>