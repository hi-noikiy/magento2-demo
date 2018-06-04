<?php

namespace Iksula\ColorSwatch\Setup;

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

		$installer->run('create table color_swatch(swatch_id int(11) not null auto_increment,product_color varchar(50),image text,created_date timestamp NULL DEFAULT CURRENT_TIMESTAMP, primary key(swatch_id))');


		

		}

        $installer->endSetup();

    }
}