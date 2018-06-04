<?php
namespace Iksula\Storeinventory\Controller\Storeinventory;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;


class Index extends Action
{

    protected $productCollectionfactory;
    protected $storeinventoryFactory;
    protected $iOnlineInventory;
    protected $directoryList;
    private $_transportBuilder;
    protected $storemanagerFactory;

    public function __construct(Context $context
                                , \Magento\Catalog\Model\ProductFactory  $productCollectionfactory
                                ,\Iksula\Storeinventory\Model\StoreinventoryFactory $storeinventoryFactory
                                ,\Iksula\Storemanager\Model\StoremanagerFactory $storemanagerFactory
                                ,\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
                                ,\Magento\Framework\Filesystem\DirectoryList $directoryList)
    {

        $this->productCollectionfactory = $productCollectionfactory;
        $this->storeinventoryFactory = $storeinventoryFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->directoryList = $directoryList;
        $this->storemanagerFactory = $storemanagerFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */

     public function OutStockAllProducts(){

          $productCollection = $this->productCollectionfactory
                                  ->create()
                                  ->getCollection()
                                  ->addFinalPrice();
                                  /*if(count($error_skus>0)) {
                                  $productCollection->addFieldToFilter('sku', array('nin' => $error_skus));
                                  }*/
                                 $productCollection ->getData();

                                  foreach($productCollection as $productData){

                                    try{
                                        $objectManagerstock = \Magento\Framework\App\ObjectManager::getInstance();
                                       $StockState = $objectManagerstock->get('\Magento\CatalogInventory\Api\StockStateInterface');
                                       $pqty = (int) $StockState->getStockQty($productData['entity_id']);


                                       $objectManagertest = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                                       $resourcetest = $objectManagertest->get('Magento\Framework\App\ResourceConnection');
                                       $connectiontest = $resourcetest->getConnection();
                                       $tableNametest = 'magento_inventory'; //gives table name with prefix

                                       //Delete Data from table
                                       $sqltest = "INSERT INTO ".$tableNametest ." (`sku`, `qty`, `price`) VALUES ( '".$productData['sku']."', '".$pqty."', '".$productData['final_price']."')";

                                       $connectiontest->query($sqltest);



                                       $objectManager1 = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                                       $resource1 = $objectManager1->get('Magento\Framework\App\ResourceConnection');
                                       $connection1 = $resource1->getConnection();
                                       $tableName1 = 'cataloginventory_stock_item'; //gives table name with prefix

                                       //Delete Data from table
                                       $sql1 = "UPDATE ".$tableName1 ." set qty = 0 , is_in_stock = 0 where product_id = ". $productData['entity_id'];

                                       $connection1->query($sql1);


                                        $objectManager2 = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                                        $resource2 = $objectManager2->get('Magento\Framework\App\ResourceConnection');
                                        $connection2 = $resource2->getConnection();
                                        $tableName2 = 'cataloginventory_stock_status'; //gives table name with prefix

                                        //Delete Data from table
                                        $sql2 = "UPDATE ".$tableName2 ." set qty = 0 , stock_status = 0  where product_id = ". $productData['entity_id'];

                                        $connection2->query($sql2);

                                    }catch(Exception $e){
                                      echo $e->getMessage();
                                    }

                                  }
     }



    public function execute()
    
    {
      $allstorecodes = $this->storemanagerFactory->create()->getCollection();
      foreach ($allstorecodes as  $value) {
        $store_codes[] = $value->getStoreCode();
      }
      $folderpath = $this->directoryList->getpath('var').'/import/store_inventory/';
      $date = date('d-m-Y');
      $fullpath = $folderpath.'storeinventory_data_'.$date.'.csv';
      echo $filename = 'storeinventory_data_'.$date.'.csv';
      echo "<br>";
      $aStoreInventory = array();
      $duplicate_skus = array();
      $wrong_storecode = array();
      $wrong_storecode_skus = array();
      $aUpdateStoreInventoryChuck = array();
      $invalid_skus = array();
      $unsetkeys = array();

      if(file_exists($fullpath)){
        $time_start = microtime(true);
        $file = fopen($fullpath, 'r');

        $stored  = array();
        $i=0;
        $j=0;
        fgetcsv($file);
        while(($data = fgetcsv($file,1000, ',')) !== false) {

            //check duplicate skus
            if (in_array($data[0], $stored)) { $duplicate_skus[] = $data[0];}

            //check wrong store code
            if (!in_array($data[1], $store_codes)) { $wrong_storecode[] = $data[1];$wrong_storecode_skus[] = $data[0];}

            //remember inserted value
            $stored[] = $data[0];
            $aStoreInventory[$i]['sku'] = $data[0];
            $aStoreInventory[$i]['store_code'] = $data[1];
            $aStoreInventory[$i]['buffer_inventory'] = $data[2];
            $aStoreInventory[$i]['wholeinventory'] = $data[3];
            $i++;
        }
        $duplicate_skus = array_unique($duplicate_skus);
        $wrong_storecode = array_unique($wrong_storecode);

        //Start Remove wrong store code entries
        if(count($wrong_storecode) > 0)
        {
          foreach ($wrong_storecode as  $value) {
            $keys = array_keys(array_column($aStoreInventory, 'store_code'), $value);
            if(count($keys)>0) {
                foreach($keys as $value1)
                  { $unsetkeys[] = $value1; }
                
            } 
          }
       }

        $unsetkeys = array_unique($unsetkeys);
        if(count($unsetkeys)>0) {
          foreach($unsetkeys as $value1)
                { unset($aStoreInventory[$value1]); 
                }
              
          }
        //End Remove wrong store code entries

        //Start Remove duplicate skus entries
        if(count($duplicate_skus) > 0)
        {
          foreach ($duplicate_skus as  $value) {
            $keys = array_keys(array_column($aStoreInventory, 'sku'), $value);
            if(count($keys)>0) {
                foreach($keys as $value1)
                 { 
                  if($j!= 0) { $unsetkeys[] = $value1;  } // Set only first line another entries unset for same sku
                  //$unsetkeys[] = $value1; 
                  $j++;
                 }
            } 
          }
        }

        $unsetkeys = array_unique($unsetkeys);
        if(count($unsetkeys)>0) {
          foreach($unsetkeys as $value1)
                { unset($aStoreInventory[$value1]); 
                }
              
          }
        //End Remove duplicate skus entries
        


        ////////////////////////////////////////////////

        //$file = fopen($fullpath, 'r');
        $i = 0 ;
          /*while (($line = fgetcsv($file)) !== FALSE) {
                $aStoreInventory [] = $line;
          }*/

          //array_shift($aStoreInventory);

           $iOnlineInventory = array();
           if(!empty($aStoreInventory)){
            $error_skus = array();
             try{
              $error_skus = array_merge($duplicate_skus,$wrong_storecode_skus);
               $this->OutStockAllProducts();

             $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
              $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
              $connection = $resource->getConnection();
              $tableName = 'storeinventory_tb'; //gives table name with prefix

              //Delete Data from table
              $sql = "Truncate table " . $tableName;
              $connection->query($sql);
            }catch(Exception $e){
              echo $e->getMessage();
            }
//echo "<pre>";print_r($aStoreInventory);exit;
            foreach($aStoreInventory as $storeData){
                 $iSku = trim($storeData['sku']);
                 $wholeinventory = (int)$storeData['wholeinventory'];
                 $bufferinventory = (int)$storeData['buffer_inventory'];
                 $store_code = $storeData['store_code'];
                 $ProductQuantitystatus = ($bufferinventory > $wholeinventory ?  false : true);

                   $productLoadCheck = $this->productCollectionfactory->create()->loadByAttribute('sku' , $iSku);

                   if(empty($productLoadCheck)){
                          $invalid_skus[] = $iSku;
                          continue;
                   }

                 if (array_key_exists($iSku,$iOnlineInventory))
                  {
                                $OldInventory = $iOnlineInventory[$iSku];

                                if(!$ProductQuantitystatus){

                                  $NewInventory = $OldInventory +  0 ;
                                }else{
                                  $NewInventory = $OldInventory +  ($wholeinventory - $bufferinventory) ;
                                }

                                $iOnlineInventory[$iSku] = $NewInventory;
                  }else{
                                if(!$ProductQuantitystatus){

                                  $NewInventory =  0 ;
                                }else{
                                  $NewInventory = ($wholeinventory - $bufferinventory) ;
                                }

                                $iOnlineInventory [$iSku] = $NewInventory;
                  }


                    $data = array('sku'=>$iSku,'store_id'=> $store_code ,'original_price'=> 0.00
                            , 'ecomm_price' => 0.00 , 'buffer_inventory' => $bufferinventory, 'inventory' => $wholeinventory);


                  $model = $this->storeinventoryFactory->create()->setData($data);
                  try {
                          $insertId = $model->save()->getId();
                          //echo "Data successfully inserted. Insert ID: ".$insertId.'<br />';
                      } catch (Exception $e){
                       echo $e->getMessage();
                  }

            }
        }

        if(!empty($iOnlineInventory)){
          foreach($iOnlineInventory as  $sku => $inventory){
                if($inventory > 0){
                    $stockData = 1;
                }else{
                  $stockData = 0 ;
                }

                      $productLoad = $this->productCollectionfactory->create()->loadByAttribute('sku' , $sku);

                      if(!empty($productLoad)){
                        try{
                          $objectManager1 = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                           $resource1 = $objectManager1->get('Magento\Framework\App\ResourceConnection');
                           $connection1 = $resource1->getConnection();
                           $tableName1 = 'cataloginventory_stock_item'; //gives table name with prefix

                           //Delete Data from table
                            $sql1 = "UPDATE ".$tableName1 ." set qty = ".$inventory." , is_in_stock = ".$stockData." where product_id = ". $productLoad->getId();

                           $connection1->query($sql1);


                           $objectManager2 = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                            $resource2 = $objectManager2->get('Magento\Framework\App\ResourceConnection');
                            $connection2 = $resource2->getConnection();
                            $tableName2 = 'cataloginventory_stock_status'; //gives table name with prefix

                            //Delete Data from table
                            $sql2 = "UPDATE ".$tableName2 ." set qty = ".$inventory." , stock_status = ".$stockData."  where product_id = ". $productLoad->getId();


                            $connection2->query($sql2);

                        }catch(Exception $e){
                          echo $e->getMessage();
                         }
                        // $productLoad->setStockData(['qty' => $inventory, 'is_in_stock' => $stockData]);
                        // $productLoad->setQuantityAndStockStatus(['qty' => $inventory, 'is_in_stock' => $stockData]);
                        // $productLoad->save();
                        //}
                      }else{
                        echo $sku .' dont exist';
                        echo '<br />';
                      }

          }
        }

        // Trigger email on duplicate / wrong store code / Invalid Skus
         if(count($duplicate_skus) > 0 || count($wrong_storecode) > 0 || count($invalid_skus) > 0)
        {
            $msg = "Duplicate SKUS & Wrong Store Code & Invalid SKUS:";
            $this->duplicateSkusMail($msg,$duplicate_skus,$wrong_storecode_skus,$invalid_skus);
        }
        
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        $msg = "Storeinventory Uploaded successfully";
        $this->inventoryDumpMail($msg,$time);
        $this->reindexmanual();
        $this->cacheflushmanual();

      }else{
        $msg = "Storeinventory File not exist";
        $time = 0;
        $this->inventoryDumpMail($msg,$time);
        exit('File dont exist');
      }

    }


    public function reindexmanual(){


      $output = shell_exec('php bin/magento indexer:reindex');
        echo "<pre>$output</pre>";
        echo 'Reindex is done';
        echo '<br />';

    }

    public function cacheflushmanual(){

      $output = shell_exec('php bin/magento cache:clean');
        echo "<pre>$output</pre>";

        echo 'Cache is clean';
        echo '<br />';

        $output = shell_exec('php bin/magento cache:flush');
          echo "<pre>$output</pre>";
        echo 'Cache is flush';
        echo '<br />';

    }

    public function inventoryDumpMail($msg,$time)
    {
            //$emailids = 'firospk@2xlme.com,vishal.p@iksula.com';
            $emailids = 'vishal.p@iksula.com';
            $emailids = explode(',', $emailids);
            $transport = $this->_transportBuilder->setTemplateIdentifier('storeinventory_mail_template')
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
                ->setTemplateVars([
                    'message'     => $msg,
                    'time'      => $time,
                    ])
                ->setFrom('general')
                ->addTo($emailids)
                ->getTransport();
            $transport->sendMessage();
    }

    public function duplicateSkusMail($msg,$skus,$wrong_storecode_skus,$invalid_skus)
    {   

            //$emailids = 'firospk@2xlme.com,vishal.p@iksula.com';
            $emailids = 'vishal.p@iksula.com,deepali.s@iksula.com';
            $emailids = explode(',', $emailids);
            $skus = implode(',', $skus);
            $wrong_storecode_skus = implode(',', $wrong_storecode_skus);
            $invalid_skus = implode(',', $invalid_skus);
            $transport = $this->_transportBuilder->setTemplateIdentifier('duplicateskus_mail_template')
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
                ->setTemplateVars([
                    'message'     => $msg,
                    'skus'      => $skus,
                    'wrong_storecode_skus'   => $wrong_storecode_skus,
                    'invalid_skus'   => $invalid_skus,
                    ])
                ->setFrom('general')
                ->addTo($emailids)
                ->getTransport();
            $transport->sendMessage();
    }
}
