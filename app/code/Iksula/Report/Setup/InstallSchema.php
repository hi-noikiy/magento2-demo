<?php

namespace Iksula\Report\Setup;

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

		$installer->run('create table sales_custom_attributes(custom_id int not null auto_increment, order_week varchar(100), sales_item_id int(11), product_id int(11), order_id int(11), brand varchar(100), dimensions varchar(100), weight varchar(100),height varchar(100) ,width varchar(100), depth varchar(100), color varchar(100), discount_amount float(10,2), discount_percent float(10,2),product_code varchar(100),product_qty int(10),payment_title varchar(100),shipping_address text,country_name varchar(100),amount_refund_user varchar(100),sub_payment_mode varchar(100),delivery_store_no varchar(100),delivery_store_name varchar(100), price float(10,2), special_price float(10,2),increment_id varchar(100),qty int(10), primary key(custom_id))');
		}

        $installer->endSetup();

    }
}