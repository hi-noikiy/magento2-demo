<?php
namespace Iksula\Price\Plugin;

class FormatPrice
{
    /*
    * Returns an array with price formatting info
    *
    * \Magento\Framework\Locale\Format $subject
    */
    public function aroundGetPriceFormat(\Magento\Framework\Locale\Format $subject, callable $proceed, $localeCode = null, $currencyCode = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $enable = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('price_section/general/enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if($enable){
            $returnValue = $proceed($localeCode, $currencyCode);

            $returnValue['requiredPrecision'] = 0;

            return $returnValue;
        }
        else{
           return $proceed($localeCode, $currencyCode);
        }
    }
}