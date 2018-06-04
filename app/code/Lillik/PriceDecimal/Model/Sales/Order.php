<?php
    
    namespace Lillik\PriceDecimal\Model\Sales;

    class Order extends \Magento\Sales\Model\Order
    {
      public function formatPrice($price, $addBrackets = false)
      {
          return $this->formatPricePrecision($price, 0, $addBrackets);
      }


      public function formatBasePrice($price)
      {
          return $this->formatBasePricePrecision($price, 0);
      }

    }
