<?php
namespace Iksula\Commonmodule\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;
 
class Data extends AbstractHelper
{
	   protected $imgObj; 
	   protected $_registry;

	   public function __construct(\Magento\Catalog\Helper\Image $imgObj,\Magento\Framework\Registry $registry){
	   		$this->_registry = $registry;
 			$this->imgObj = $imgObj;
	   }

	    public function getCurrentCategory()
	    {        
	        return $this->_registry->registry('current_category');
	    }
	   
	   public function getSmallGallaryImage($reloadedProduct)
       {
   			try
			{
   				$imgPath = $this->imgObj->init($reloadedProduct, 'mouseover_image')->setImageFile($reloadedProduct->getFile())->getUrl();
      			return $imgPath;
      		}
      		catch(Exception $e)
			{
				$this->messageManager->addError("Product Attribute not found ".$e->getMessage());
			}
       }

}