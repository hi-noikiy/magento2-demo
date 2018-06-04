<?php

namespace Iksula\Cart\Plugin;

class Cart
{
	public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
	{
	    //Re-order the cart items
	    $items = array_reverse($result['items']);
	    $result['items'] = $items;
	    return $result;
	}
}