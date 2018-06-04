<?php

namespace Iksula\Storemanager\Setup;

/* irrelevant */
#use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
/* irrelevant */
#use Magento\Framework\Setup\SchemaSetupInterface;
/* add this */
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements  UpgradeDataInterface
{
    
    public function upgrade(ModuleDataSetupInterface $setup,
                            ModuleContextInterface $context){
        $installer = $setup;
        $setup->startSetup();

            if (version_compare($context->getVersion(), '1.0.1') < 0) {
            /** @var CustomerSetup $customerSetup */
                $installer->run('ALTER TABLE storemanager
                                ADD role_id_mapping int(255)');

        }

        $setup->endSetup();
        
    }

}