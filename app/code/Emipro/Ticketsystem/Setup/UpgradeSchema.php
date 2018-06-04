<?php
namespace Emipro\Ticketsystem\Setup;
 
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
 
class UpgradeSchema implements UpgradeSchemaInterface {
 
    public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
        $installer = $setup;
        $installer->startSetup();
        
        if(version_compare($context->getVersion(), '1.1.0', '<')) {
            
            $installer->run("ALTER TABLE {$setup->getTable('emipro_ticket_system')} AUTO_INCREMENT=1000");
            
            // Get tutorial_simplenews table
			$tableName = $setup->getTable('emipro_ticket_response');
			// Check if the table already exists
			if ($installer->getConnection()->isTableExists($tableName) != true) {
				// Create tutorial_simplenews table
				$table = $installer->getConnection()
						->newTable($tableName)
						->addColumn(
								'response_id', Table::TYPE_INTEGER, null, [
							'identity' => true,
							'unsigned' => true,
							'nullable' => false,
							'primary' => true
								], 'Response ID'
						)
						->addColumn(
								'response_title', Table::TYPE_TEXT, null, ['nullable' => false], 'Response Title'
						)
						->addColumn(
								'response_text', Table::TYPE_TEXT, null, ['nullable' => false], 'Response Text'
						)
						->addColumn(
								'status', Table::TYPE_INTEGER, null, ['nullable' => false], 'status'
						)
						->setComment('Ticket frequent response')
						->setOption('type', 'InnoDB')
						->setOption('charset', 'utf8');
				$installer->getConnection()->createTable($table);
			}
            
            
            
            $installer->getConnection()->addColumn(
                $installer->getTable( 'emipro_ticket_system' ),
                'sender_name', Table::TYPE_TEXT, null, ['nullable' => false], 'Sender Name'
            );
            $installer->getConnection()->addColumn(
                $installer->getTable( 'emipro_ticket_conversation' ),
                'discussion_admin', Table::TYPE_TEXT, null, ['nullable' => false], 'Discussion Admin'
            );
            $installer->getConnection()->addColumn(
                $installer->getTable( 'emipro_ticket_conversation' ),
                'discussion_admin_name', Table::TYPE_TEXT, null, ['nullable' => false], 'Discussion Admin Name'
            );
            $installer->getConnection()->addColumn(
                $installer->getTable( 'emipro_ticket_conversation' ),
                'current_admin_name', Table::TYPE_TEXT, null, ['nullable' => false], 'Current Admin Name'
            );
            
        }
 
        $installer->endSetup();
    }
}
