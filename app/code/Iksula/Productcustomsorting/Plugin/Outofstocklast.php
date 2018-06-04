<?php

namespace Iksula\Productcustomsorting\Plugin;

class Outofstocklast
{

    public function afterGetProductCollection(\Magento\Catalog\Model\Layer $subject ,  $collection)
      {

            $collection_outstocksort =   $collection->setOrder('stock.is_in_stock' , 'desc');
              return $collection_outstocksort;

      }


}
