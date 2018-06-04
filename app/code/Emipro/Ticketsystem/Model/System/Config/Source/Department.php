<?php

namespace Emipro\Ticketsystem\Model\System\Config\Source;

class Department extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface {

    public function toOptionArray() {
        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $object_manager->get('Emipro\Ticketsystem\Helper\Data');
        return $helper->getTicketdept();
    }

}
