<?php
namespace Iksula\NavisionApi\Controller\Storeinventory;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;


class Index extends Action
{

    protected $productCollectionfactory;
    protected $storeinventoryFactory;
    protected $iOnlineInventory;
    protected $navisionapi;

    public function __construct(Context $context
                                , \Magento\Catalog\Model\ProductFactory  $productCollectionfactory
                                ,\Iksula\Storeinventory\Model\StoreinventoryFactory $storeinventoryFactory
                                ,\Magento\Framework\Filesystem\DirectoryList $directoryList
                                ,\Iksula\NavisionApi\Helper\Data $navisionapi)
    {

        $this->productCollectionfactory = $productCollectionfactory;
        $this->storeinventoryFactory = $storeinventoryFactory;
        $this->directoryList = $directoryList;
        $this->navisionapi = $navisionapi;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */




    public function execute()
    {


      $this->navisionapi->callInventoryMasterApi();
        $this->reindexmanual();
        $this->cacheflushmanual();



    }


    public function reindexmanual(){


      // $indexerCollection = $this->_indexerCollectionFactory->create();
      //   $allIds = $indexerCollection->getAllIds();
      //
      //   foreach ($allIds as $id) {
      //       $indexer = $this->_indexerFactory->create()->load($id);
      //       //$indexer->reindexRow($id); // or you can use reindexRow according to your need
      //       $indexer->reindexAll(); // this reindexes all
      //   }


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
}
