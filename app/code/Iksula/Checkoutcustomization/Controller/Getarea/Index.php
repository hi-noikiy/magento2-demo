<?php

namespace Iksula\Checkoutcustomization\Controller\Getarea;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Index extends Action
{


	 protected $resultJsonFactory;

	public function __construct(
		Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
	)
	{	
		parent::__construct($context);
		$this->resultJsonFactory = $resultJsonFactory; 

	}

	public function execute()
	{  
			$region_id = $this->getRequest()->getParam('region_id');

			$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$tableName = $resource->getTableName('directory_area_region'); //gives table name with prefix
			try {
				if(!$region_id){
					$response = [
	                            'errors' => true,
	                            'message' => __('No options'),
	                            'optionsvalue' => ""
	                        ];
				}else{

				$sql = "Select area_code FROM " . $tableName ." where region_id =".$region_id." order by area_code asc" ;
				$result = $connection->fetchAll($sql); // gives associated array, table fields as key in array.			
				if($result){
					$html = "";
					$html .= "<option value=''>Please select Area</option>";					
					foreach ($result as $value) {
						$html .= "<option value='".__($value['area_code'])."'>".__($value['area_code'])."</option>";			
					}					
					$response = [
	                            'errors' => false,
	                            'message' => __('Success'),
	                            'optionsvalue' => $html
	                        ];
				}else{
					$response = [
	                            'errors' => true,
	                            'message' => __('No options'),
	                            'optionsvalue' => ""
	                        ];
				}
			 	
				}

			 } catch (Exception $e) {
			 	$response = [
	                            'errors' => true,
	                            'message' => __($e->getMessage()),
	                            'optionsvalue' => ""
	                        ];
			 } 
		 $resultJson = $this->resultJsonFactory->create();
         return $resultJson->setData($response);

	}
	
}