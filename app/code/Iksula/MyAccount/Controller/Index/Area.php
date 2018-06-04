<?php

namespace Iksula\MyAccount\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Area extends \Magento\Framework\App\Action\Action
{
    /*public function execute()
    {

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }*/

    protected $area;

	protected $request;

	public function __construct(
		Context $context,
		\Magento\Framework\App\Request\Http $request,
		\Iksula\Carriercodetelephone\Model\AreaData $area
	)
	{	
		$this->area = $area;
		$this->request = $request;
		parent::__construct($context);

	}

	public function execute()
	{
		$params = $this->request->getParams();
		$region_id = $params['region_id'];
		$area_data = array();

			$area_code = $this->area->getCollection()->addFieldToFilter('region_id', array('eq' => $region_id))->setOrder('area_code', 'asc');


			
			foreach($area_code as $iCollectionsCode){

				$area_data [] = $iCollectionsCode['area_code'];

			}

			echo json_encode($area_data);			
	}
}