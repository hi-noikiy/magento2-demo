<?php
namespace Iksula\Storemanager\Model\ResourceModel;

class Storemanager extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('storemanager', 'storemanager_id');
    }
}
?>