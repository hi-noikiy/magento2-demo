<?php
namespace Iksula\Ordersplit\Controller\Adminhtml\ordersplits;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Getstoresbysku extends \Magento\Backend\App\Action
{



    protected $storeinventoryFactory;

    protected $storemanagerFactory;

    
    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context,
            \Iksula\Storeinventory\Model\StoreinventoryFactory $storeinventoryFactory 
            , \Iksula\Storemanager\Model\StoremanagerFactory $storemanagerFactory
            )
    {
            $this->storeinventoryFactory = $storeinventoryFactory;
            $this->storemanagerFactory = $storemanagerFactory; 
           parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
         $sku = $this->getRequest()->getParam('sku');
         $qty = $this->getRequest()->getParam('qty');

         $aStores = $this->_storeinventoryFactory->create()->getCollection()
                               ->addFieldToFilter('sku' , array('eq' => $sku))
                               ->addFieldToFilter('inventory' , array('gteq' => $qty))
                               ->getData();


                               $sStoresData = "<option value=''>--Please Select Stores--</option>";
                          foreach($aStores as $stores){
                                $store_object = $this->storemanagerFactory->create()->load($stores['store_id'] , 'store_code');
                                $store_id = $store_object->getStoremanagerId();   
                                $store_name = $store_object->getStoreName();   

                                $sStoresData .= "<option value='".$store_id."'>".$store_name."</option>";

                          }
        
                $result['htmlconent']=$sStoresData;
         $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }
}