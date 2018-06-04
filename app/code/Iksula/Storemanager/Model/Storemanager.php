<?php
namespace Iksula\Storemanager\Model;

class Storemanager extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Storemanager\Model\ResourceModel\Storemanager');
    }
}
?>