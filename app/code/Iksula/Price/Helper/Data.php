<?php

namespace Iksula\Price\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

/**
* 
*/
class Data extends AbstractHelper
{
	private $price;
	public function __construct(\Magento\Directory\Model\PriceCurrency $price){
		$this->price = $price;
    }

    public function getRoundPrice($price){
    	return $this->price->format($price, true,0,null,null);
    }
}