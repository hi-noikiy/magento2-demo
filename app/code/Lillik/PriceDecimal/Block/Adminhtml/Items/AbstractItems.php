<?php

namespace Lillik\PriceDecimal\Block\Adminhtml\Items;


class AbstractItems extends \Magento\Sales\Block\Adminhtml\Items\AbstractItems
{


  public function displayPrices($basePrice, $price, $strong = false, $separator = '<br />')
  {

    echo 'test';
    exit;

      return $this->displayRoundedPrices($basePrice, $price, 0, $strong, $separator);
  }

}
