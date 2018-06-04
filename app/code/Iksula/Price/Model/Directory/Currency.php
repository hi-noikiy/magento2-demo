<?php
namespace Iksula\Price\Model\Directory;

class Currency extends \Magento\Directory\Model\Currency
{
    /*
    * You can set precision from here in $options array
    */
    public function formatTxt($price, $options = [])
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $enable = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('price_section/general/enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);


        if (!is_numeric($price)) {
                $price = $this->_localeFormat->getNumber($price);
            }
        $price = sprintf("%F", $price);

        if($enable){
            $options['precision'] = 0;
        }
        else{
            $options['precision'] = 2;
        }
        return $this->_localeCurrency->getCurrency($this->getCode())->toCurrency($price, $options);
    }
}