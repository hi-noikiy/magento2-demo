<?php
namespace Iksula\Checkoutcustomization\Model\ResourceModel;

class Chequedetails extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('order_cheque_details', 'id');
    }
}
?>