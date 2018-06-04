<?php
namespace Iksula\Carriercodetelephone\Model\ResourceModel;

class Carriercodedata extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('carriercode_tbl', 'id');
    }
}
?>