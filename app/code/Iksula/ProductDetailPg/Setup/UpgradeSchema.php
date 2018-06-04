<?php

namespace Iksula\ProductDetailPg\Setup;
 
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
 
class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;
 
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
 
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
 
        if ( version_compare( $context->getVersion(), '1.0.1', '<' ) ) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'product_color');
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'product_color', [
                'type'       => 'int',
                'input'      => 'select',
                'label'      => 'Colour',
                'sort_order' => 1000,
                'required'   => false,
                'global'     => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'    => '',
                'option'     => [
                    'values' => [
                        0 => 'Black',
                        1 => 'White',
                        2 => 'Blue',
                        3 => 'Green',
                        4 => 'Yellow',
                        5 => 'Pink',
                        6 => 'Grey',
                        7 => 'Red',
                        8 => 'Orange',
                        9 => 'Brown',
                    ]
                ]
            ]);            
        }

        if ( version_compare( $context->getVersion(), '1.0.2', '<' ) ) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
 
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'room_type');
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'room_type', [
                'type'       => 'int',
                'input'      => 'select',
                'label'      => 'Room Type',
                'sort_order' => 1001,
                'required'   => false,
                'global'     => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'    => '',
                'option'     => [
                    'values' => [
                        0 => 'Living Room',
                    ]
                ]
            ]);
        }
        if ( version_compare( $context->getVersion(), '1.0.3', '<' ) ) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
 
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'brand');
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'brand', [
                'type'       => 'int',
                'input'      => 'select',
                'label'      => 'Brand',
                'sort_order' => 1002,
                'required'   => false,
                'global'     => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'    => '',
                'option'     => [
                    'values' => [
                        0 => '2XL',
                        1 => 'Aldo',
                        2 => 'Call it Spring',
                    ]
                ]
            ]);
        } 
    $setup->endSetup();    
    }
}
