<?php

namespace Iksula\Carriercodetelephone\Controller\Telephonevalidation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Index extends Action
{

	protected $carriercodefactory;

	public function __construct(
		Context $context,
		\Iksula\Carriercodetelephone\Model\CarriercodedataFactory  $carriercodefactory
	)
	{	
		$this->carriercodefactory = $carriercodefactory;
		parent::__construct($context);

	}

	public function execute()
	{  
			$aCollectioncode = $this->carriercodefactory->create()->getCollection();

			foreach($aCollectioncode as $iCollectionsCode){

				$aCodeData [] = $iCollectionsCode['country_code'];

			}

			echo json_encode($aCodeData);
			//echo 'hshshshsh';
	}
	
}