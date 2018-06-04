<?php

namespace Iksula\Ordersplit\Controller\Reindexcache;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Index extends Action {


  protected $resultPageFactory;
  protected $resultFactory;
  protected $helper;
  protected $_indexerFactory;
  protected $_indexerCollectionFactory;


  public function __construct(
    \Magento\Framework\Controller\ResultFactory $resultFactory,
    \Iksula\NewArrivals\Helper\Data $helper,
    \Magento\Framework\App\Action\Context $context,
    \Magento\Framework\View\Result\PageFactory $resultPageFactory,
    \Magento\Indexer\Model\IndexerFactory $indexerFactory,
    \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
    \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
      \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool

	)
	{
    $this->_indexerFactory = $indexerFactory;
    $this->_indexerCollectionFactory = $indexerCollectionFactory;
    $this->resultFactory = $resultFactory;
    $this->helper = $helper;
    $this->resultPageFactory = $resultPageFactory;
    $this->_cacheTypeList = $cacheTypeList;
       $this->_cacheFrontendPool = $cacheFrontendPool;

    parent::__construct($context);

	}

    public function execute() {

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

      $types = array('config','layout','block_html','collections','reflection','db_ddl','eav','config_integration','config_integration_api','full_page','translate','config_webservice');
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }

                echo 'Cache Flush  is done';
                echo '<br />';

    }

}
