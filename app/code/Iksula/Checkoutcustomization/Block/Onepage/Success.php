<?php

namespace Iksula\Checkoutcustomization\Block\Onepage;

class Success extends \Magento\Checkout\Block\Onepage\Success
{
    public function getEmail()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        return $order->getCustomerEmail();
    }
}