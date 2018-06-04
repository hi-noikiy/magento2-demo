<?php
namespace Iksula\Carriercodetelephone\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;
 
class Data extends AbstractHelper
{
	   protected $storemanger; 
	   protected $nationiltyObj;

	   public function __construct(\Magento\Store\Model\StoreManagerInterface $storemanger,
	   								\Iksula\Carriercodetelephone\Model\Entity\Attribute\Nationality\Options $nationiltyObj){
 			$this->storemanger = $storemanger;
 			$this->nationiltyObj = $nationiltyObj;
	   }

	   public function getBaseUrl()
       {
   		
   		$baseurl = $this->storemanger->getStore()
           ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
      	return $baseurl;
       }


       public function getNationalityData(){

       		return $this->nationiltyObj->getAllOptions();


       }

}