<?php

namespace Iksula\Ordersplit\Setup;

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
                $installer->run('ALTER TABLE ordersplits_tb
                                ADD order_items_data text');

        }

           if (version_compare($context->getVersion(), '1.0.2') < 0) {
            /** @var CustomerSetup $customerSetup */
                $installer->run('ALTER TABLE sales_order
                                ADD ordersplit_status int(2)');

        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            /** @var CustomerSetup $customerSetup */
                $installer->run('ALTER TABLE sales_order
                                ALTER ordersplit_status SET DEFAULT 0 ');
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            /** @var CustomerSetup $customerSetup */
                $installer->run('create table rejection_history_tb(id int not null auto_increment, order_id varchar(255) , ordersplit_id varchar(255) , ordersplit_uniqueid varchar(255) ,  rejection_comment text , rejected_storeid varchar(255) ,  primary key(id))');

        }

        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            /** @var CustomerSetup $customerSetup */
            $installer->run('ALTER TABLE ordersplits_tb
                            ADD column invoiced_status int(2) DEFAULT 0 ');

        }

        if (version_compare($context->getVersion(), '1.0.6') < 0) {
            /** @var CustomerSetup $customerSetup */
            $installer->run('ALTER TABLE ordersplits_tb
                            ADD column invoice_id varchar(255) ');

        }


        if (version_compare($context->getVersion(), '1.0.7') < 0) {
            /** @var CustomerSetup $customerSetup */
            $installer->run('ALTER TABLE ordersplits_tb
                            ADD column picklist_sent int(2) Default 0');

        }

        if (version_compare($context->getVersion(), '1.0.8') < 0) {
            /** @var CustomerSetup $customerSetup */
            $installer->run('ALTER TABLE ordersplits_tb
                            ADD column shipment_id varchar(255)');

                            $installer->run('ALTER TABLE ordersplits_tb
                                            ADD column shipment_status int(2) DEFAULT 0');

        }

        if (version_compare($context->getVersion(), '1.0.9') < 0) {
            /** @var CustomerSetup $customerSetup */
            $installer->run('ALTER TABLE ordersplits_tb
                            ADD column awb_link varchar(255)');


        }

        $setup->endSetup();

    }

}
