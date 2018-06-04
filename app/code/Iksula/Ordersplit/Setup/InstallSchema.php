<?php

namespace Iksula\Ordersplit\Setup;

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

		$installer->run('create table ordersplits_tb(id int not null auto_increment, order_id varchar(225), order_item_id varchar(225), picklist_id varchar(225), order_date datetime , sku varchar(255) , order_item_status varchar(255) , order_status varchar(255) , invoice varchar(255) , shipment varchar(255) , allocated_storeids varchar(255) ,  primary key(id))');


		//demo 
//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//$scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
//demo 

		}

        $installer->endSetup();

    }
}