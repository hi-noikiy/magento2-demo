<?php
namespace Iksula\Fetchrapi\Model\ResourceModel;

class Fetchr extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('fetchr_api', 'id');
    }
}
?>