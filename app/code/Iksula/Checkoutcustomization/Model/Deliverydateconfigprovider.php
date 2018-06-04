<?php
 
namespace Iksula\Checkoutcustomization\Model;
 
use Magento\Checkout\Model\ConfigProviderInterface;
 
class Deliverydateconfigprovider implements ConfigProviderInterface
{
	const STARTDATE = 'checkoutcustomization_section/general/delivery_date_startdate';
	const DAYSCOUNT = 'checkoutcustomization_section/general/delivery_days_count';
	const CARTNOTE = 'cart_section/general/cart_note';

	protected $carriercodefactory;
	
	public function __construct(
	    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	    \Iksula\Carriercodetelephone\Model\CarriercodedataFactory  $carriercodefactory
	)
	{    
	    $this->scopeConfig = $scopeConfig;
	    $this->carriercodefactory = $carriercodefactory;

	}
	public function getDeliverydatestartdate() {
	 $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
	 return $this->scopeConfig->getValue(self::STARTDATE, $storeScope); //you get your value here
	}

	public function getDeliverydatedayscount() {
	 $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
	 return $this->scopeConfig->getValue(self::DAYSCOUNT, $storeScope); //you get your value here
	}

	public function getCartnote() {
	 $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
	 return $this->scopeConfig->getValue(self::CARTNOTE, $storeScope); //you get your value here
	}

	public function getCountrycode() {
			$country_codeData = array();

			$country_code = $this->carriercodefactory->create()->getCollection()->addFieldToSelect('country_code');


			$html = "";
			foreach($country_code as $iCollectionsCode){

				$country_codeData [] = $iCollectionsCode['country_code'];
				$html.= "<option value='".$iCollectionsCode['country_code']."'>".$iCollectionsCode['country_code']."</option>";
			}

			// return json_encode($country_codeData);
			// return $country_codeData;
			return $html;
	
	}

	public function getCarriercode() {
			$country_codeData = array();

			$country_code = $this->carriercodefactory->create()->getCollection()->addFieldToSelect('carrier_code');


			$html = "";
			foreach($country_code as $iCollectionsCode){

				$country_codeData [] = $iCollectionsCode['carrier_code'];
				$html.= "<option value='".$iCollectionsCode['carrier_code']."'>".$iCollectionsCode['carrier_code']."</option>";

			}

			// return json_encode($country_codeData);
			// return $country_codeData;
			return $html;
	}



    public function getConfig()
    { 
        $config = [];                
        $config['delivery_date_start_date'] = $this->getDeliverydatestartdate();
        $config['delivery_date_days_count'] = $this->getDeliverydatedayscount();
        $config['country_code'] = $this->getCountrycode();
        $config['carrier_code'] = $this->getCarriercode();        
        $config['cart_note'] = $this->getCartnote();        
        return $config;
    }
}