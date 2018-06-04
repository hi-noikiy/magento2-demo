<?php
namespace Iksula\Fetchrapi\Model;

class Fetchr extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Fetchrapi\Model\ResourceModel\Fetchr');
    }
}
?>