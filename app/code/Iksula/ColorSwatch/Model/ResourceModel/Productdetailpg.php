<?php
namespace Iksula\ColorSwatch\Model\ResourceModel;

class Productdetailpg extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('color_swatch', 'swatch_id');
    }
}
?>