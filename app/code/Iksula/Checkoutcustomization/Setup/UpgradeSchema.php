<?php
namespace Iksula\Checkoutcustomization\Setup;


use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        
        $installer = $setup;
 
        $installer->startSetup();
       if(version_compare($context->getVersion(), '1.0.1', '<')) {                    
            
            // Get module table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('order_cheque_details')
            )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'ID'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Order Id'
            )
            ->addColumn(
                'bank_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Bank Name'
            )
            ->addColumn(
                'cheque_no',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Cheque No'
            )
            ->addColumn(
                'cheque_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                255,
                ['nullable => false'],
                'Cheque Amount'
            )
            ->addColumn(
                'date_of_cheque',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['nullable => false'],
                'Date of Cheque'
            )                      
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Created At'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Updated At'
            )
            ->setComment('Order Cheque Details');
            $installer->getConnection()->createTable($table);        
        }

       $installer->endSetup();
    }
    
}
