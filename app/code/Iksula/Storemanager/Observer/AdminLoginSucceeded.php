<?php
namespace Iksula\Storemanager\Observer;
use \Magento\Store\Model\StoreRepository;
use Magento\Store\Model\Store;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class AdminLoginSucceeded implements \Magento\Framework\Event\ObserverInterface
{


    protected $_storeManagerInterface;
    protected $_storeRepository;

  public function __construct(
                                 \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
                                 StoreRepository $storeRepository,
                                 HttpContext $httpContext,
    StoreCookieManagerInterface $storeCookieManager
  ){
          $this->_storeManagerInterface = $storeManagerInterface;
          $this->_storeRepository = $storeRepository;
          $this->httpContext = $httpContext;
          $this->storeCookieManager = $storeCookieManager;
  }

  public function execute(\Magento\Framework\Event\Observer $observer)
  {
    $store = $this->_storeRepository->getActiveStoreByCode('default');
    $this->httpContext->setValue(Store::ENTITY, 'admin', 'default');
    $this->storeCookieManager->setStoreCookie($store);
    //$this->_storeManagerInterface->setCurrentStore(1);

    /*$stores = $this->_storeRepository->getList();
    //print_r($stores->getData());
        $websiteIds = array();
        $storeList = array();
        foreach ($stores as $store) {
            echo $websiteId = $store["website_id"];
            echo $storeId = $store["store_id"];
            $storeName = $store["name"];
            $storeList[$storeId] = $storeName;
            array_push($websiteIds, $websiteId);
        }
        //return $storeList;
        //echo '<pre>';
        //  print_r($storeList);

       
    //echo "login success";
    $currentStore = $this->_storeManagerInterface->getStore();
    //echo $currentStoreId = $currentStore->getId();
    //$currentStoreCode = $currentStore->getCode();
    $this->_storeManagerInterface->setCurrentStore(1);
    //die();
     //$this->_storeManagerInterface->setCurrentStore(1);*/

     /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
     /*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
     $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
     echo $storeId = (int) $this->getRequest()->getParam('store', 1);
     $store = $storeManager->getStore($storeId);
     echo $store->getCode();
     die();
     $storeManager->setCurrentStore($store->getCode());*/
   
  }
}
