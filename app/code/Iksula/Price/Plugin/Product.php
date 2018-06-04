<?php
 
namespace Iksula\Price\Plugin;
use Magento\Framework\Exception\InputException;

class Product
{
    /*public function afterGetPrice(\Magento\Catalog\Model\Product $subject, $result)
    {
    		
        return round($result);
    }*/

    public function aroundConvert($subject, $proceed, $price, $toCurrency = null)
    {
        $price = $proceed($price, $toCurrency);   
        $options['precision'] = 0;
        // you logic
        // warning ... logic affects the price of shipping  
        return $price;
    }
}