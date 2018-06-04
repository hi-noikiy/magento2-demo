<?php

namespace Iksula\Storeinventory\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements  UpgradeDataInterface
{

    public function upgrade(ModuleDataSetupInterface $setup,
                            ModuleContextInterface $context){
        $installer = $setup;
        $setup->startSetup();

            if (version_compare($context->getVersion(), '1.0.1') < 0) {
           $installer->run('create table magento_inventory(id int not null auto_increment, sku varchar(255), qty int(10) , price decimal(10,2),created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,  primary key(id))');

        }

        $setup->endSetup();

    }

}
