<?php

namespace Iksula\CategoryList\Block\Index;


class Index extends \Magento\Framework\View\Element\Template {

    protected $_categoryFactory;
    protected $_category;
    protected $_categoryHelper;
    protected $_categoryRepository;
    protected $_categoryCollectionFactory;

    public function __construct(
    	\Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
     array $data = []) {

        $this->_categoryFactory = $categoryFactory;
        $this->_categoryHelper = $categoryHelper;
        $this->_categoryRepository = $categoryRepository;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
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

    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->_categoryHelper->getStoreCategories($sorted , $asCollection , $toLoad);
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

/************************** category tree updated **************************/



public function getLevel2Category(){
        $collection = $this->_categoryCollectionFactory
        ->create()
        ->addAttributeToSelect('*')
        ->addAttributeToFilter('level' , array('eq' => 2));
        //->getData();
        return $collection;

}

public function getLevel3Category($category_id){
        $collection = $this->_categoryCollectionFactory
        ->create()
        ->addAttributeToSelect('*')
        ->addAttributeToFilter('parent_id' , array('eq' => $category_id));
        //->getData();
        return $collection;

}

public function getLevel4Category($category_id){
        $collection = $this->_categoryCollectionFactory
        ->create()
        ->addAttributeToSelect('*')
        ->addAttributeToFilter('parent_id' , array('eq' => $category_id));
        //->getData();
        return $collection;

}

/**********************************************************************************/




}
