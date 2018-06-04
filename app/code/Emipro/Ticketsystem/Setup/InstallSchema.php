<?php

namespace Emipro\Ticketsystem\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface {

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();

        // Get tutorial_simplenews table
        $tableName = $installer->getTable('emipro_ticket_department');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            // Create tutorial_simplenews table
            $table = $installer->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                            'department_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                            ], 'Department ID'
                    )
                    ->addColumn(
                            'department_name', Table::TYPE_TEXT, null, ['nullable' => false, 'default' => ''], 'Department Name'
                    )
                    ->addColumn(
                            'admin_user_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'Admin User ID'
                    )
                    ->addColumn(
                            'status', Table::TYPE_INTEGER, null, ['nullable' => false], 'status'
                    )
                    ->setComment('Ticket Department')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }

        $tableStatus = $installer->getTable('emipro_ticket_status');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableStatus) != true) {
            // Create tutorial_simplenews table
            $status = $installer->getConnection()
                    ->newTable($tableStatus)
                    ->addColumn(
                            'status_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                            ], 'Status ID'
                    )
                    ->addColumn(
                            'status', Table::TYPE_TEXT, null, ['nullable' => false, 'default' => ''], 'Status'
                    )
                    ->setComment('Ticket Status')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($status);
        }


        $tablePriority = $installer->getTable('emipro_ticket_priority');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tablePriority) != true) {
            // Create tutorial_simplenews table
            $priority = $installer->getConnection()
                    ->newTable($tablePriority)
                    ->addColumn(
                            'priority_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                            ], 'Priority ID'
                    )
                    ->addColumn(
                            'priority', Table::TYPE_TEXT, null, ['nullable' => false, 'default' => ''], 'Priority'
                    )
                    ->setComment('Ticket Priority')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($priority);
        }

        $tableTicket = $installer->getTable('emipro_ticket_system');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableTicket) != true) {
            // Create tutorial_simplenews table
            $ticket = $installer->getConnection()
                    ->newTable($tableTicket)
                    ->addColumn(
                            'ticket_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                            ], 'Ticket ID'
                    )
                    ->addColumn(
                            'orderid', Table::TYPE_TEXT, null, ['nullable' => false], 'Orderid'
                    )
                    ->addColumn(
                            'customer_id', Table::TYPE_TEXT, null, ['nullable' => false], 'Customer ID'
                    )
                    ->addColumn(
                            'customer_email', Table::TYPE_TEXT, null, ['nullable' => false], 'Customer Email'
                    )
                    ->addColumn(
                            'customer_name', Table::TYPE_TEXT, null, ['nullable' => false], 'Customer Name'
                    )
                    ->addColumn(
                            'admin_user_id', Table::TYPE_TEXT, null, ['nullable' => false], 'Admin User ID'
                    )
                    ->addColumn(
                            'assign_admin_id', Table::TYPE_TEXT, null, ['nullable' => false], 'Assign Admin ID'
                    )
                    ->addColumn(
                            'subject', Table::TYPE_TEXT, null, ['nullable' => false], 'Subject'
                    )
                    ->addColumn(
                            'status_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'unsigned' => true], 'Status ID'
                    )
                    ->addColumn(
                            'priority_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'unsigned' => true], 'Priority ID'
                    )
                    ->addColumn(
                            'department_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'unsigned' => true], 'Department ID'
                    )
                    ->addColumn(
                            'date', Table::TYPE_TIMESTAMP, null, ['nullable' => false], 'Create Date'
                    )
                    ->addColumn(
                            'lastupdated_date', Table::TYPE_TIMESTAMP, null, ['nullable' => false], 'Last Updated Date'
                    )
                    ->addColumn(
                            'external_id', Table::TYPE_TEXT, null, ['nullable' => false], 'External ID'
                    )
                    ->addColumn(
                            'unique_id', Table::TYPE_TEXT, null, ['nullable' => false], 'External ID'
                    )
                    ->addColumn(
                            'store_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'External ID'
                    )
                    ->addForeignKey(
                            $installer->getFkName('emipro_ticket_system', 'status_id', 'emipro_ticket_status', 'status_id'), 'status_id', $installer->getTable('emipro_ticket_status'), 'status_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE, \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                            $installer->getFkName('emipro_ticket_system', 'priority_id', 'emipro_ticket_priority', 'priority_id'), 'priority_id', $installer->getTable('emipro_ticket_priority'), 'priority_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE, \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                            $installer->getFkName('emipro_ticket_system', 'department_id', 'emipro_ticket_department', 'department_id'), 'department_id', $installer->getTable('emipro_ticket_department'), 'department_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE, \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    ->setComment('Ticket System')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($ticket);
            
        }

        $tableConversation = $installer->getTable('emipro_ticket_conversation');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableConversation) != true) {
            // Create tutorial_simplenews table
            $conversation = $installer->getConnection()
                    ->newTable($tableConversation)
                    ->addColumn(
                            'conversation_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                            ], 'Conversation ID'
                    )
                    ->addColumn(
                            'ticket_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'unsigned' => true], 'Ticket ID'
                    )
                    ->addColumn(
                            'status_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'unsigned' => true], 'Status ID'
                    )
                    ->addColumn(
                            'current_admin', Table::TYPE_TEXT, null, ['nullable' => false], 'Current Admin ID'
                    )
                    ->addColumn(
                            'name', Table::TYPE_TEXT, null, ['nullable' => false], 'Name'
                    )
                    ->addColumn(
                            'message', Table::TYPE_TEXT, null, ['nullable' => false], 'Message'
                    )
                    ->addColumn(
                            'message_type', Table::TYPE_TEXT, null, ['nullable' => false], 'Message Type'
                    )
                    ->addColumn(
                            'date', Table::TYPE_TIMESTAMP, null, ['nullable' => false], 'Date'
                    )
                    ->addForeignKey(
                            $installer->getFkName('emipro_ticket_conversation', 'status_id', 'emipro_ticket_status', 'status_id'), 'status_id', $installer->getTable('emipro_ticket_status'), 'status_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE, \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                            $installer->getFkName('emipro_ticket_conversation', 'ticket_id', 'emipro_ticket_system', 'ticket_id'), 'ticket_id', $installer->getTable('emipro_ticket_system'), 'ticket_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE, \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    ->setComment('Ticket Conversation')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($conversation);
        }
        $tableAttachment = $installer->getTable('emipro_ticket_attachment');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableAttachment) != true) {
            // Create tutorial_simplenews table
            $attachment = $installer->getConnection()
                    ->newTable($tableAttachment)
                    ->addColumn(
                            'attachment_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                            ], 'Ticket ID'
                    )
                    ->addColumn(
                            'conversation_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'unsigned' => true,], 'Orderid'
                    )
                    ->addColumn(
                            'file', Table::TYPE_TEXT, null, ['nullable' => false], 'Customer ID'
                    )
                    ->addColumn(
                            'current_file_name', Table::TYPE_TEXT, null, ['nullable' => false], 'Admin User ID'
                    )
                    ->addForeignKey(
                            $installer->getFkName('emipro_ticket_attachment', 'conversation_id', 'emipro_ticket_conversation', 'status_id'), 'conversation_id', $installer->getTable('emipro_ticket_conversation'), 'conversation_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE, \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    ->setComment('Ticket Attachment')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($attachment);
        }

        $installer->endSetup();
    }

}
