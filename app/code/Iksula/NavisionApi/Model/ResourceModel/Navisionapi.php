<?php
namespace Iksula\NavisionApi\Model\ResourceModel;

class Navisionapi extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('navisionapi_logs', 'api_increment_id');
    }
}
?>