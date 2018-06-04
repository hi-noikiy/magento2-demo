<?php
namespace Iksula\Orderreturns\Model;

class Orderreturn extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Orderreturns\Model\ResourceModel\Orderreturn');
    }
}
?>