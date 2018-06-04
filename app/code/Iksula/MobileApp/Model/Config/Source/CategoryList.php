<?php
/**
 * Copyright © 2016 Oscprofessionals® All Rights Reserved.
 */
namespace Iksula\MobileApp\Model\Config\Source;

class CategoryList implements \Magento\Framework\Option\ArrayInterface
{

	protected $_categoryHelper;

    public function __construct(
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
     	array $data = []) {
        	$this->_categoryHelper = $categoryHelper;
            $this->_storeManager = $storeManager;
    }
	public function toOptionArray()
	{
	 	$categories = $this->getCustomCatCollection();
			foreach ($categories as $category) {
				if($category->getId() != 1 && $category->getId() !=2){
			     $result[] = 
				    ['value' => $category->getId(), 'label' => __($category->getName())];
				}
		    }
	  return $result;
	}
	/**
	 * Returns all categories which are active and include in menu.
	 */
    public function getCustomCatCollection(){
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
		$categories = $categoryFactory->create()                              
		    ->addAttributeToSelect('*')
		    ->addAttributeToFilter('is_active', 1)
		    ->addAttributeToFilter('include_in_menu', 1)
		    ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched

		return $categories;
    }
}