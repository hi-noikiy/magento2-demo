<?php
namespace Iksula\NavisionApi\Model;

class Navisionapi extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\NavisionApi\Model\ResourceModel\Navisionapi');
    }
}
?>