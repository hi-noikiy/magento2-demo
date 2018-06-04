<?php

namespace Iksula\Checkoutcustomization\Setup;

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

                      if (version_compare($context->getVersion(), '1.0.2') < 0) {

                      /** @var CustomerSetup $customerSetup */
                        //           $states = [
                        //     'delivered' => [
                        //         'label' => __('Delivered'),
                        //         'statuses' => ['delivered' => ['default' => '1']],
                        //         'visible_on_front' => true,
                        //     ],
                        // ];
                        //
                        // foreach ($states as $code => $info) {
                        //     if (isset($info['statuses'])) {
                        //         foreach ($info['statuses'] as $status => $statusInfo) {
                        //             $data[] = [
                        //                 'status' => $status,
                        //                 'state' => $code,
                        //                 'is_default' => is_array($statusInfo) && isset($statusInfo['default']) ? 1 : 0,
                        //             ];
                        //         }
                        //     }
                        // }
                        // $setup->getConnection()->insertArray(
                        //     $setup->getTable('sales_order_status_state'),
                        //     ['status', 'state', 'is_default'],
                        //     $data
                        // );
                  }

                  if (version_compare($context->getVersion(), '1.0.3') < 0) {

                  /** @var CustomerSetup $customerSetup */
                              $setup->run('Alter table sales_order add customer_emirates_id varchar(255) NULL ');
              }
                      $setup->endSetup();



    }

}
