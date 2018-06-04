<?php

namespace Iksula\Storemanager\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

        protected $storemanagerFactory;


        public function __construct( \Iksula\Storemanager\Model\StoremanagerFactory $storemanagerFactory
                                    ){

            $this->storemanagerFactory = $storemanagerFactory;
        }

       public function getStoreCodeByStoreId($store_id){

          $store_code = $this->storemanagerFactory->create()->load($store_id)->getStoreCode();

          return $store_code;

        }

        public function getStoreIdByStoreCode($store_code){

          $store_id = $this->storemanagerFactory->create()->load($store_code , 'store_code')->getStoremanagerId();

          return $store_id;

        }

        public function getStoreManagerObject($store_id){

          $store_obj = $this->storemanagerFactory->create()->load($store_id);

          return $store_obj;

        }

}
