<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksula\Price\Block\Weee\Item\Price;

class Renderer extends \Magento\Tax\Block\Item\Price\Renderer
{
   public function formatPrice1($price)
    {
        // echo "aaaaaaaaaaaaaa";exit;
        $item = $this->getItem();
        if ($item instanceof QuoteItem) {
            return 0;
            return $this->priceCurrency->format(
                $price,
                true,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $item->getStore()
            );
        } elseif ($item instanceof OrderItem) {
            return $item->getOrder()->formatPrice($price);
        } else {
            return $item->getOrderItem()->getOrder()->formatPrice($price);
        }
    }
}
