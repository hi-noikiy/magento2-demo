<?php
namespace Iksula\Carriercodetelephone\Model\ResourceModel;

class AreaData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('directory_area_region', 'area_id');
    }
}
?>