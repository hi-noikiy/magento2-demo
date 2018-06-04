<?php
 
namespace Iksula\ProductDetailPg\Setup;
 
use Magento\Eav\Setup\EavSetup; 
use Magento\Eav\Setup\EavSetupFactory /* For Attribute create  */;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory; 
        /* assign object to class global variable for use in other class methods */
    }
 
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
         
        /**
         * Add attributes to the eav/attribute
         */		
		
		$eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'dimensions');
		$eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'dimensions',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Dimensions',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => ''
            ]
        );

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'assembly');
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'assembly',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Assembly',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => ''
            ]
        );
		
		$eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'height');
		$eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'height',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Height',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => ''
            ]
        );

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'width');
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'width',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Width',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => ''
            ]
        );

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'depth');
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'depth',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Depth',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => ''
            ]
        );

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'sku_mapping');
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'sku_mapping',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'SKU Mapping',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => ''
            ]
        );
    }
}