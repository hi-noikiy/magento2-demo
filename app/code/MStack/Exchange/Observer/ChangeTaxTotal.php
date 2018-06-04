<?php
namespace MStack\Exchange\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer;

class ChangeTaxTotal implements ObserverInterface
{
    public $additionalTaxAmt = 7;
    public $additionalSubtotalAmt = 3;


    public function execute(Observer $observer)
    {
        /** @var Magento\Quote\Model\Quote\Address\Total */
		$total = $observer->getData('total');

        //make sure tax value exist
        if (count($total->getAppliedTaxes()) > 0) {
            $total->addTotalAmount('tax', $this->additionalTaxAmt);
            $total->addTotalAmount('base_subtotal', $this->additionalSubtotalAmt);
        }
		// echo '<pre>';
		// print_r($total);

        return $this;
    }
}