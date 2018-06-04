<?php

namespace Iksula\Storemanager\Setup;

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

		$installer->run('create table storemanager(storemanager_id int not null auto_increment, store_name varchar(100) , store_code varchar(100) , store_username varchar(100), store_country varchar(100) , store_state varchar(100) , store_city varchar(100) , store_pincode varchar(100) , store_longitude varchar(100) , store_latitude varchar(100) , store_type varchar(100) , store_address text , store_mobileno varchar(100) , store_emailid varchar(100), store_status int(2)  , primary key(storemanager_id))');


		//demo 
//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//$scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
//demo 

		}

        $installer->endSetup();

    }
}