<?php
namespace Iksula\Checkoutcustomization\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class CheckoutCartProductAddAfterObserver implements ObserverInterface
{
    private $_checkoutSession;

    public function __construct(
        CheckoutSession $checkoutSession
    )
    {
        $this->_checkoutSession = $checkoutSession;
    }

    public function execute(EventObserver $observer)
    {
        echo "save delivery address";
        die();
        /** @var \Magento\Catalog\Model\Product $product */
        //$product = $observer->getEvent()->getDataByKey('product');

        /** @var \Magento\Quote\Model\Quote\Item $item */
        //$item = $this->_checkoutSession->getQuote()->getItemByProduct($product);

        //echo $itemId = $item->getId();
       
    }
}