<?php
/**
* Copyright © 2013-2017 Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Iksula\NewArrivals\Helper;
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
    const XML_PATH_CAT_ID = 'new_arrival/category/category_id';
    const XML_PATH_PAGE_LIMIT = 'new_arrival/page_limit/new_arrival';
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

    public function getPageLimit(){
        return $this->scopeConfig->getValue(self::XML_PATH_PAGE_LIMIT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    public function addNewArrivalProducts(){
        $categoryId = $this->getConfigCatId();
        $this->removeProductsFromCategory($categoryId);
        $this->assignProductsToCategory($categoryId);
        $this->reIndexing();
        //$this->cleanAndFlushCache();
    }
    /**
     * Load product collection of only sale category 
     *
     * @return Array
     */
    public function removeProductsFromCategory($categoryId)
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
    public function assignProductsToCategory($categoryId){
        $product_ids = '';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $page_limit = $this->getPageLimit();
        $productCollection =$objectManager->create('Magento\Catalog\Model\Product')
            ->getCollection()
            ->addFieldToFilter('created_at', array('gt' => date("Y-m-d H:i:s", strtotime('-15 day'))))
            ->addAttributeToFilter('status', ['eq' => 1])            
            ->setOrder('created_at','DESC')
            ->addAttributeToSelect('*');

        $product_count = count($productCollection->getData());

        if($product_count > 0){           
            $this->_resourceIterator->walk($productCollection->getSelect()->limit($page_limit),[[$this, 'assignedProduct']]);
        }

        $product_data = $productCollection->getData();
        
        for($i=0; $i<$product_count; $i++)
        {
            $product_ids .= $product_data[$i]['entity_id'].", ";
        }

        $product_ids_value = rtrim($product_ids, ", ");
        if($product_count < $page_limit)
        {
            $left_product = $page_limit - $product_count;
            
            $productCollectionNew =$objectManager->create('Magento\Catalog\Model\Product')
            ->getCollection()
            ->addAttributeToFilter('new_arrivals','1')
            ->addAttributeToFilter('status', ['eq' => 1])
            ->addFieldToFilter('entity_id', array('nin' => array($product_ids_value))) 
            ->setOrder('created_at','DESC')
            ->addAttributeToSelect('*');

            $this->_resourceIterator->walk($productCollectionNew->getSelect()->limit($left_product),[[$this, 'assignedProduct']]);
        }
        
        
    
    }
    /**
     * Add Products to sale category  
     *
     */
    public function assignedProduct($args){
        $categoryId = $this->getConfigCatId();
        $product = $this->_productFactory->create()->load($args['row']['entity_id']);
        $existingCategories = $product->getCategoryIds();
        array_push($existingCategories,$categoryId);
        $product->setCategoryIds($existingCategories);
        $product->save();
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
    public function cleanAndFlushCache()
    {
        $this->cacheManager->flush($this->cacheManager->getAvailableTypes());
        $this->cacheManager->clean($this->cacheManager->getAvailableTypes());
    }
}