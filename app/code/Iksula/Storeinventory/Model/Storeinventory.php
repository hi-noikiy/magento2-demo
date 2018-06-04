<?php
namespace Iksula\Storeinventory\Model;

class Storeinventory extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Storeinventory\Model\ResourceModel\Storeinventory');
    }
}
?>