<?php

namespace Iksula\Orderitemstatus\Setup;

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
                $installer->run("INSERT INTO orderitemstatus_tb (name , code) 
                    VALUES 
                    ('Payment Pending' , 'payment_pending'),
                    ('Pending verification Pending' , 'payment_verification_pending'),
                    ('Verified order' , 'processing'),
                    ('Allocated' , 'allocated'),
                    ('Rejected' , 'rejected'),
                    ('Shipped' , 'shipped'),
                    ('Delivered' , 'delivered'),
                    ('Cash at store' , 'cash_at_store'),
                    ('Cheque at store' , 'cheque_at_store'),
                    ('Cheque submitted' , 'cheque_submitted'),
                    ('Cheque failed' , 'cheque_failed'),
                    ('Cancelled' , 'cancelled')
                    ");
            }
          

        $setup->endSetup();
        
    }

}