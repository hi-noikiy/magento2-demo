<?php
namespace Iksula\Feedback\Model;

class Feedback extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Iksula\Feedback\Model\ResourceModel\Feedback');
    }
}
?>