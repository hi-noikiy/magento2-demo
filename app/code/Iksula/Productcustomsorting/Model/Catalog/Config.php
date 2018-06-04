<?php
namespace Iksula\Productcustomsorting\Model\Catalog;

class Config extends \Magento\Catalog\Model\Config
{
    /**
     * Get product name
     *
     * @return string
     * @codeCoverageIgnoreStart
     */
   public function getAttributeUsedForSortByArray()
    {
        $options  ['product_sort_order'] = __('Any');
        $options ['created_at'] =  __('Newest');
        $options['price_desc'] = __('Price High to Low');
        $options['price_asc'] = __('Price Low to High');


        foreach ($this->getAttributesUsedForSortBy() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
            $options[$attribute->getAttributeCode()] = $attribute->getStoreLabel();
        }

        return $options;
    }
}
