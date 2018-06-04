<?php

namespace Iksula\Carriercodetelephone\Controller\Telephonevalidation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Carriercode extends Action
{

	protected $carriercodefactory;

	protected $request;

	public function __construct(
		Context $context,
		\Magento\Framework\App\Request\Http $request,
		\Iksula\Carriercodetelephone\Model\CarriercodedataFactory  $carriercodefactory
	)
	{	
		$this->carriercodefactory = $carriercodefactory;
		$this->request = $request;
		parent::__construct($context);

	}

	public function execute()
	{  

		$params = $this->request->getParams();
		$country_code = $params['country_code'];
		$aCarrierCodeData = array();

			$aCarriercode = $this->carriercodefactory->create()->getCollection()->addFieldToFilter('country_code', array('eq' => $country_code));


			
			foreach($aCarriercode as $iCollectionsCode){

				$aCarrierCodeData [] = $iCollectionsCode['carrier_code'];

			}

			echo json_encode($aCarrierCodeData);			
	}
	
}