<?php
namespace Iksula\Storemanager\Controller\Adminhtml\storemanager;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;


class Checkuseremailidexist extends \Magento\Backend\App\Action
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
        $email_id = $this->getRequest()->getParam('email_id');
        $userModel = $this->_userFactory->create()->getCollection()
                            ->addFieldToFilter('email' , array('eq' => $email_id));                

        if(count($userModel) >= 1){

            $result['status'] = 0;
            $result['message'] = 'Email id already exist';            
        }else{
            $result['status'] = 1;
            $result['message'] = 'Email id not exist';
        }
         $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }
}