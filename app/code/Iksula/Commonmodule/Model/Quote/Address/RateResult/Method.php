<?php
namespace Iksula\Commonmodule\Model\Quote\Address\RateResult;
 class Method 
{

 public function afterSetPrice($subject)
    {
    	$shipping_title = $subject->getCarrierTitle() ." - ". $subject->getMethodTitle();
      if ($shipping_title === "Standard - delivery") 
     	{
      	  $subject->setCarrierTitle("");
      	  $subject->setMethodTitle("");
	  	} 

    return null;
    }
}