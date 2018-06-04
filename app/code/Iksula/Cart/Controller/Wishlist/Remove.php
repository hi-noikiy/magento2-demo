<?php

namespace Iksula\Cart\Controller\Wishlist;


class Remove extends \Magento\Framework\App\Action\Action
{
	protected $wishlist;
	public function __construct(
	    \Magento\Wishlist\Model\Wishlist $wishlist
	) {
	    $this->wishlist = $wishlist;
	}

	public function execute()
	{ echo "remove wishlist"; exit;
	    /*$customerId = 1;
	    $productId = 6;
	    $wish = $this->wishlist->loadByCustomerId($customerId);
	    $items = $wish->getItemCollection();

	    // @var \Magento\Wishlist\Model\Item $item 
	    foreach ($items as $item) {
	        if ($item->getProductId() == $productId) {
	            $item->delete();
	            $wish->save();
	        }
	    }*/
	}
}