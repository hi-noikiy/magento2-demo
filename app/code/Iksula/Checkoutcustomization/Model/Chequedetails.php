<?php
namespace Iksula\Checkoutcustomization\Model;

class Chequedetails extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Checkoutcustomization\Model\ResourceModel\Chequedetails');
    }
}
?>