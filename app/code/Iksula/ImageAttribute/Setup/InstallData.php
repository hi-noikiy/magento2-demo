<?php

namespace Iksula\ImageAttribute\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{

/**
 * @var EavSetupFactory
 */
private $eavSetupFactory;

/**
 * InstallData constructor.
 */
public function __construct(
    EavSetupFactory $eavSetupFactory
)
{
    $this->eavSetupFactory = $eavSetupFactory;
}

/**
 * Installs data for a module
 *
 * @param ModuleDataSetupInterface $setup
 * @param ModuleContextInterface   $context
 *
 * @return void
 */
public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
{
    $setup->startSetup();

    /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
    $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

    $eavSetup->addAttribute(
        Product::ENTITY,
        'mouseover_image',
        [
            'type'                    => 'varchar',
            'label'                   => 'Mouseover',
            'input'                   => 'media_image',
            'frontend'                => 'Magento\Catalog\Model\Product\Attribute\Frontend\Image',
            'required'                => false,
            'global'                  => ScopedAttributeInterface::SCOPE_STORE,
            'used_in_product_listing' => true,
        ]);

      $setup->endSetup();
    }
}