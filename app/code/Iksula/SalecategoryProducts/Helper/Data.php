<?php
/**
* Copyright © 2013-2017 Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Iksula\SalecategoryProducts\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_resourceIterator;
	/**
	* @var \Magento\Framework\App\Config\ScopeConfigInterface
	*/
	protected $scopeConfig;
    protected $_categoryFactory;
    protected $_productCollectionFactory;
    protected $_productFactory;
    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $_indexerFactory;
    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory
     */
    protected $_indexerCollectionFactory;
    protected $cacheManager;
	const XML_PATH_CAT_ID = 'salecategoryproduct/setting/categoryid';
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
        \Magento\Framework\App\Cache\Manager $cacheManager

	){
        $this->_resourceIterator = $resourceIterator;
		$this->scopeConfig = $scopeConfig;
        $this->_categoryFactory = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_productFactory = $productFactory;
		$this->_indexerFactory = $indexerFactory;
		$this->_indexerCollectionFactory = $indexerCollectionFactory;
		$this->cacheManager = $cacheManager;
	}
    /**
     * Get Category Id from configuration
     *
     * @return int
     */
	public function getConfigCatId(){
		return $this->scopeConfig->getValue(self::XML_PATH_CAT_ID,
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
	}

    public function addAllDiscountedProductsToSaleCategory(){
        $categoryId = $this->getConfigCatId();
        $this->removeProductsFromSalesCategory($categoryId);
        $this->assignProductsToSaleCategory($categoryId);
        $this->reIndexing();
        //$this->cleanAndFlushCache();
    }

    /**
     * Load product collection of only sale category 
     *
     * @return Array
     */
    public function removeProductsFromSalesCategory($categoryId)
    {
        $category = $this->_categoryFactory->create()->load($categoryId);
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addCategoryFilter($category)->load();
        $this->_resourceIterator->walk($collection->getSelect(),[[$this, 'removedProduct']]);
    }
    /**
     * Remove all products in sale category
     *
     */
    public function removedProduct($args){
        $categoryId = $this->getConfigCatId();
        $product = $this->_productFactory->create()->load($args['row']['entity_id']);
        $existingCategories = $product->getCategoryIds();
        $key = array_search($categoryId, $existingCategories);
        if (false !== $key) {
            unset($existingCategories[$key]);
        }
        $product->setCategoryIds($existingCategories);
        $product->save();
    }
    /**
     * Load all collection of products having special price  
     *
     * @return Array
     */
    public function assignProductsToSaleCategory($categoryId){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection =$objectManager->create('Magento\Catalog\Model\Product')
            ->getCollection()
            ->addAttributeToFilter('price', ['neq' => ''])
            ->addAttributeToFilter('special_price', ['neq' => ''])
            ->addAttributeToFilter('status', ['eq' => 1])
            ->addAttributeToSelect('*');
		$this->_resourceIterator->walk($productCollection->getSelect(),[[$this, 'assignedProduct']]);
    }
    /**
     * Add Products to sale category  
     *
     */
	public function assignedProduct($args){
		$categoryId = $this->getConfigCatId();
		if(isset($args['row']['special_price']) && $args['row']['special_price'] !=''){
    		 $price = round($args['row']['price']);
    		 $specialPrice = round($args['row']['special_price']);
    		 if($price > $specialPrice){
    			$product = $this->_productFactory->create()->load($args['row']['entity_id']);
    			$existingCategories = $product->getCategoryIds();
    			array_push($existingCategories,$categoryId);
    			$product->setCategoryIds($existingCategories);
    			$product->save();
    		 }
	   }
	}

    /**
     * Reindex Catalog  
     *
     */
    public function reIndexing(){
        $indexerCollection = $this->_indexerCollectionFactory->create();
        $indexerIds = $indexerCollection->getAllIds();
        foreach ($indexerIds as $indexerId) {
            if(($indexerId == 'catalog_category_product') || ($indexerId == 'catalog_product_category') || ($indexerId == 'catalog_product_price')){
                $indexer = $this->_indexerFactory->create()->load($indexerId);
                $indexer->reindexAll();
            }
        }
                
    }
    /**
     * Clean and flush cache  
     *
     */
    /*public function cleanAndFlushCache()
    {
        $this->cacheManager->flush($this->cacheManager->getAvailableTypes());
        $this->cacheManager->clean($this->cacheManager->getAvailableTypes());
    }*/
}
