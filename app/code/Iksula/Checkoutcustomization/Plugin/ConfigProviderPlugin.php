<?php
namespace Iksula\Checkoutcustomization\Plugin;

class ConfigProviderPlugin extends \Magento\Framework\Model\AbstractModel
{

    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {

        $items = $result['totalsData']['items'];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        for($i=0;$i<count($items);$i++){

            $quoteId = $items[$i]['item_id'];
            $quote = $objectManager->create('\Magento\Quote\Model\Quote\Item')->load($quoteId);
            $productId = $quote->getProductId();
            $product = $objectManager->create('\Magento\Catalog\Model\Product')->load($productId);
            $productSku = $product->getResource()->getAttribute('sku')->getFrontend()->getValue($product);         
            if($productSku == ''){
                $productSku = '';
            }
            $items[$i]['sku'] = $productSku;
        }
        $result['totalsData']['items'] = $items;
        return $result;
    }

}