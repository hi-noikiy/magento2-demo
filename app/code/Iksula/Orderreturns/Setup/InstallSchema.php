<?php

namespace Iksula\Orderreturns\Setup;

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

		$installer->run('CREATE TABLE IF NOT EXISTS `custom_return_reason` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_reason` text NOT NULL,
  `status` int(1) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1');
$installer->run('CREATE TABLE IF NOT EXISTS `custom_order_returns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `return_reason` varchar(11) NOT NULL,
  `product_sku` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `return_status` int(11) NOT NULL,
  `comment` text NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_price` int(11) NOT NULL,
  `return_price` int(11) NOT NULL,
  `pickup_time` varchar(255) NOT NULL,
  `pickup_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1');


		

		}

        $installer->endSetup();

    }
}