<?php

namespace Emipro\Ticketsystem\Model\System\Config\Source;

class Adminuser extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface {

    public function toOptionArray() {
        $user = [];
        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $adminUserModel = $object_manager->get('Magento\User\Model\User')->getCollection();
        $option = [];
        $adminUser = $adminUserModel->getData();
        foreach ($adminUser as $value) {
            $user[] = ['value' => $value["user_id"], 'label' => __($value["firstname"] . " " . $value["lastname"])];
        }
        return $user;
    }

}
