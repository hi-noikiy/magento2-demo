<?php
/**
 * Copyright © 2016 Oscprofessionals® All Rights Reserved.
 */
namespace Iksula\MobileApp\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data.
 */
class Data extends AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    protected $_categoryFactory;
    protected $_productCollectionFactory;
    protected $_productRepositoryFactory;
    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageHelperFactory;
        protected $dataHelper;

    const XML_PATH_CATEGORY_ID = 'mobileapp/setting/cat';
    protected $_bannerFactory;
        protected $_categoryHelper;
    protected $_categoryRepository;



    /**
     * Data constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Iksula\ProductDetailPg\Helper\Data $helperData,
        \Magestore\Bannerslider\Model\ResourceModel\Banner\Collection $bannerCollection,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository
    )
    {
        $this->_storeManager = $storeManager;
        $this->_categoryFactory = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->dataHelper = $helperData;
        $this->_bannerFactory = $bannerCollection;
        $this->_categoryHelper = $categoryHelper;
        $this->_categoryRepository = $categoryRepository;
    }
    /**
     * Get Category Id from configuration
     *
     * @return int
     */
    public function getConfigCatId(){
        return $this->scopeConfig->getValue(self::XML_PATH_CATEGORY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }
    public function getBannerData(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $pubPath = $objectManager->get('Magento\Store\Model\StoreManagerInterface')
                    ->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $collection = $this->_bannerFactory->getData();
        $bannerData =[];
        $i=0;
        foreach ($collection as $key => $value) {
            $bannerData[$i]['Id'] = $value['banner_id'];
            $bannerData[$i]['Title'] = $value['name'];
            $bannerData[$i]['ImageUrl'] = $pubPath.$value['image'];                          
        $i++;
        }
        return $bannerData;
    }
    /**
     * Home page Decor block 
     * Returns collection of 4 latest products
     */
    public function getHomePageDecorBlock(){
        $categoryId = $this->getConfigCatId();
        $category = $this->_categoryFactory->create()->load($categoryId);
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->setPageSize(4); 
        $collection->setOrder('created_at','DESC');
        $collection->addCategoryFilter($category)->load();

        $decorBlockData = [];
        if(!empty($collection)){
            $i=0;
            foreach ($collection as $value) {
                $product = $this->_productRepositoryFactory->create()->get($value->getSku());
                if($value->getFinalPrice() < $value->getPrice()){
                    $specialPrice = $value->getFinalPrice();
                    $youSave = $this->dataHelper->DisplayDiscountLabel($product);
                }else{
                    $specialPrice = '';                    
                    $youSave = '';                    
                }
                $imageUrl = $this->imageHelperFactory->create()->init($product, 'product_thumbnail_image')->getUrl();
                $decorBlockData[$i]['Id'] = (int)trim($value->getEntityId());
                $decorBlockData[$i]['ProductName'] = trim($value->getName());
                $decorBlockData[$i]['Currency'] = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
                $decorBlockData[$i]['you_save'] = $youSave;
                $decorBlockData[$i]['Price'] = array(
                                                    "Price"=>$value->getPrice(),
                                                    "SpecialPrice"=>$specialPrice
                                                );
                $decorBlockData[$i]['ImageUrl'] = $imageUrl;
                $decorBlockData[$i]['Type'] = $value->getTypeId();

                $i++;
            }   
            return $decorBlockData;
        }else{
            return 'No Data Found';
        }
    }
    /**
     * Returns collection of Banner slider
     */
    public function getAllCategories(){
        $categories = $this->getStoreCategories();
        $i=0;
        $firstLevelCategories = [];
        foreach ($categories as $category) {
            $firstLevelCategories[$i]['Id'] = $category->getId();
            $firstLevelCategories[$i]['Name'] = $category->getName();
            $firstLevelCategories[$i]['is_active'] = $category->getIsActive();
            $firstLevelCategories[$i]['level'] = $category->getLevel();
            $firstLevelCategories[$i]['children'] = $this->getSecondLevelCategories($category->getId());
           $i++;
        }
        return $firstLevelCategories;

    }
    public function getSecondLevelCategories($secondlevelCatId){
        $sub_categories = $this->getCategoryById($secondlevelCatId);
        $subCategories = $sub_categories->getChildrenCategories();
        $i=0;
        $secondLevelCategories = [];
        foreach ($subCategories as $subcategory) {
            $secondLevelCategories[$i]['Id'] = $subcategory->getId();
            $secondLevelCategories[$i]['Name'] = $subcategory->getName();
            $secondLevelCategories[$i]['is_active'] = $subcategory->getIsActive();
            $secondLevelCategories[$i]['level'] = $subcategory->getLevel();
            $secondLevelCategories[$i]['children'] = $this->getThirdLevelCategories($subcategory->getId());  
            $i++;
        }
        return $secondLevelCategories;
    }
    public function getThirdLevelCategories($thirdlevelCatId){
        $sub_sub_categories = $this->getCategoryById($thirdlevelCatId);
        $subSubCategories = $sub_sub_categories->getChildrenCategories();
        $i=0;
        $thirdLevelCategories = [];
        foreach ($subSubCategories as $subSubcategory) {
            $thirdLevelCategories[$i]['Id'] = $subSubcategory->getId();
            $thirdLevelCategories[$i]['Name'] = $subSubcategory->getName(); 
            $thirdLevelCategories[$i]['is_active'] = $subSubcategory->getIsActive();
            $thirdLevelCategories[$i]['level'] = $subSubcategory->getLevel();
            $i++;
        }
        return $thirdLevelCategories;
    }
    public function getCategoryById($categoryId) 
    {
        return $this->_categoryRepository->get($categoryId);
    }
    /**
     * @param $requestUri
     * @param $authorization
     * @param $method
     */
    public function curlRequests($authorization, $method){
        $storeId = 'default';
        $header[] = "Authorization:". $authorization;
        $header[] = "Accept: application/json";
        $header[] = "Content-Type: application/json";
        $retrieveString = 'categories';
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $url = $baseUrl . 'rest/' . $storeId . '/V1/' . $retrieveString;
        $curl = curl_init($url);
        $curlOptions = $this->getCurlOptions($method, $header);
        foreach($curlOptions as $key=>$value){
            curl_setopt($curl, constant($key), $value);
        }
        return curl_exec($curl);
    }

    /**
     * @param $method
     * @param $header
     * @param $payload
     * @return array
     */
    public function getCurlOptions($method, $header)
    {
        $options = array(
            "CURLOPT_HEADER" => "TRUE",
            "CURLOPT_RETURNTRANSFER" => "TRUE",
            "CURLOPT_FOLLOWLOCATION" => "TRUE",
            "CURLOPT_MAXREDIRS" => 3,
            "CURLOPT_FRESH_CONNECT" => "FALSE",
            "CURLOPT_SSL_VERIFYPEER" => "FALSE",
            "CURLOPT_CONNECTTIMEOUT" => 30,
            "CURLOPT_TIMEOUT" => 30,
            "CURLOPT_CUSTOMREQUEST" => $method,
            "CURLOPT_HTTPHEADER" => $header
        );
        return $options;
    }
    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true) 
    {
        return $this->_categoryHelper->getStoreCategories();
    }
}
