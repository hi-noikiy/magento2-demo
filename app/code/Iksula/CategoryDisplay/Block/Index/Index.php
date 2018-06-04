<?php

namespace Iksula\CategoryDisplay\Block\Index;

class Index extends \Magento\Framework\View\Element\Template {

    protected $_categoryFactory;
    protected $_category;
    protected $_categoryHelper;
    protected $_categoryRepository;
    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $_indexerFactory;
    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory
     */
    protected $_indexerCollectionFactory;
    public function __construct(
    	\Magento\Backend\Block\Template\Context $context,        
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository, 
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
     array $data = []) {

        $this->_categoryFactory = $categoryFactory;
        $this->_categoryHelper = $categoryHelper;
        $this->_categoryRepository = $categoryRepository;
        $this->_storeManager = $storeManager;
        $this->_indexerFactory = $indexerFactory;
        $this->_indexerCollectionFactory = $indexerCollectionFactory;
        parent::__construct($context, $data);

    }


    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCategory($categoryId) 
    {
        $this->_category = $this->_categoryFactory->create();
        $this->_category->load($categoryId);        
        return $this->_category;
    }

    public function getCategoryById($categoryId) 
    {
        return $this->_categoryRepository->get($categoryId);
    }

    public function getStoreCategories($sorted = true, $asCollection = true, $toLoad = true) 
    {
        return $this->_categoryHelper->getStoreCategories();
    }

    /**
     * Get parent category object
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getParentCategory($categoryId = false)
    {
        if ($this->_category) {
            return $this->_category->getParentCategory();
        } else {
            return $this->getCategory($categoryId)->getParentCategory();        
        }        
    }
    
    /**
     * Get parent category identifier
     *
     * @return int
     */
    public function getParentId($categoryId = false)
    {
        if ($this->_category) {
            return $this->_category->getParentId();
        } else {
            return $this->getCategory($categoryId)->getParentId();
        }
    }
    
    /**
     * Get all parent categories ids
     *
     * @return array
     */
    public function getParentIds($categoryId = false)
    {
        if ($this->_category) {
            return $this->_category->getParentIds();
        } else {
            return $this->getCategory($categoryId)->getParentIds();
        }        
    }
    
    /**
     * Get all children categories IDs
     *
     * @param boolean $asArray return result as array instead of comma-separated list of IDs
     * @return array|string
     */
    public function getAllChildren($asArray = false, $categoryId = false)
    {
        if ($this->_category) {
            return $this->_category->getAllChildren($asArray);
        } else {
            return $this->getCategory($categoryId)->getAllChildren($asArray);
        }
    }
 
    /**
     * Retrieve children ids comma separated
     *
     * @return string
     */
    public function getChildren($categoryId = false)
    {
        if ($this->_category) {
            return $this->_category->getChildren();
        } else {
            return $this->getCategory($categoryId)->getChildren();
        }        
    } 
    public function disableCategory($category_id){
		$catObj = $this->_categoryFactory->create();
        $catcollection = $catObj->load($category_id);  
		$catcollection->setData('is_active', 0);
		$catcollection->save();
    }
    public function enableCategory($category_id){
		$catObj = $this->_categoryFactory->create();
        $catcollection = $catObj->load($category_id);  
		$catcollection->setData('is_active', 1);
		$catcollection->save();
    }
    public function getCustomCatCollection(){
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
		$categories = $categoryFactory->create()                              
		    ->addAttributeToSelect('*')
		    ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched

		return $categories;
    }
    /**
     * Reindex Catalog  
     *
     */
    public function reIndexing(){
        $indexerCollection = $this->_indexerCollectionFactory->create();
        $indexerIds = $indexerCollection->getAllIds();
        foreach ($indexerIds as $indexerId) {
            if(($indexerId == 'catalog_category_product') || ($indexerId == 'catalog_product_category') || ($indexerId == 'catalog_product_price') || ($indexerId == 'catalog_product_attribute') || ($indexerId == 'catalogsearch_fulltext') || ($indexerId == 'cataloginventory_stock') || ($indexerId == 'elasticsuite_categories_fulltext') || ($indexerId == 'elasticsuite_thesaurus')){
                $indexer = $this->_indexerFactory->create()->load($indexerId);
                $indexer->reindexAll();
            }
        }
                
    }

}
