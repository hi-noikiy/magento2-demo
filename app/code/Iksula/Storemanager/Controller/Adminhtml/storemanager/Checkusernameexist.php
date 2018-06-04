<?php
namespace Iksula\Storemanager\Controller\Adminhtml\storemanager;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;


class Checkusernameexist extends \Magento\Backend\App\Action
{

    protected $_userFactory;

    
    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context , \Magento\User\Model\UserFactory $userFactory)
    {
        
        $this->_userFactory = $userFactory;        
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $username = $this->getRequest()->getParam('username');
        $usernameModel = $this->_userFactory->create();
        $usernameModel->loadByUsername($username);

        if($usernameModel->getId()){

            $result['status'] = 0;
            $result['message'] = 'Username already exist';            
        }else{
            $result['status'] = 1;
            $result['message'] = 'Username not exist';
        }
         $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }
}