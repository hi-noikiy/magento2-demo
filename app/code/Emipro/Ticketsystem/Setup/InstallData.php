<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Emipro\Ticketsystem\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface {

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {

        $ticket_status = ["New", "Closed", "Reopen", "Waiting For Support", "Waiting For Customer"];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        foreach ($ticket_status as $status) {
            $stausModel = $objectManager->create("Emipro\Ticketsystem\Model\TicketStatus");
            $stausModel->setStatus($status);
            $stausModel->save();
        }
        $ticket_priority = ["Low", "Medium", "High"];
        foreach ($ticket_priority as $priority) {
            $priorityModel = $objectManager->create("Emipro\Ticketsystem\Model\TicketPriority");
            $priorityModel->setPriority($priority);
            $priorityModel->save();
        }
    }

}
