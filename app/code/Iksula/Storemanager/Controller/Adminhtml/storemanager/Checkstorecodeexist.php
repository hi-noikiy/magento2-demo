<?php
namespace Iksula\Storemanager\Controller\Adminhtml\storemanager;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;


class Checkstorecodeexist extends \Magento\Backend\App\Action
{

    protected $storemanagerFactory;

    
    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context , \Iksula\Storemanager\Model\StoremanagerFactory $storemanagerFactory)
    {
        
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
        $storecode = $this->getRequest()->getParam('storecode');
        $StoremanagerModel = $this->storemanagerFactory->create();
        $StoremanagerModel->load($storecode , 'store_code');
        
        if($StoremanagerModel->getId()){

            $result['status'] = 0;
            $result['message'] = 'StoreCode with same name already exist';            
        }else{
            $result['status'] = 1;
            $result['message'] = 'Store code not exist';
        }
         $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }
}