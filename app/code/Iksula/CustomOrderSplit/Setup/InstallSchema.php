<?php

namespace Iksula\CustomOrderSplit\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.0') < 0){

		$installer->run('create table ordersplit_dup(ordersplit_dup_id int not null auto_increment, order_id int(11), sales_order_item_id varchar(100), order_item_id varchar(225), picklist_id varchar(100), order_date datetime DEFAULT NULL, order_item_status varchar(100), order_status varchar(100),invoice varchar(100) ,shipment varchar(100), allocated_storeids varchar(100), product_sku varchar(100), inventory float(10,2), sale_inventory int(11), invoiced_status int(2), invoice_id varchar(255) DEFAULT NULL,picklist_sent int(2), shipment_id varchar(255) DEFAULT NULL, shipment_status int(2),track_number varchar(100), sku varchar(100), tax_amount float(10,2), primary key(ordersplit_dup_id))');

		}

        $installer->endSetup();

    }
}