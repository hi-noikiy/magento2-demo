<?php

namespace Iksula\Feedback\Controller\Index;

use Magento\Framework\App\Action\Context;
 
class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
	protected $_coreRegistry;
	
    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $pageFactory,  \Magento\Framework\Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
		$this->_resultPageFactory = $pageFactory;
        parent::__construct($context);
    }
	
	public function execute()
    {
		$data = $this->getRequest()->getPostValue();
		if($data):
			$resultRedirect = $this->resultRedirectFactory->create();
			try
			{
				$rowData = $this->_objectManager->create('Iksula\Feedback\Model\Feedback');
				
					@$rowData->setName($data['name']);
					@$rowData->setEmailId($data['email_id']);
					@$rowData->setMobileNo($data['mobile_no']);
					@$rowData->setFeedback($data['feedback']);
					@$rowData->setIsActive('Yes');
					$rowData->save();
						
					$this->messageManager->addSuccess(__('Feedback form submitted successfully.'));
				
			}
			catch (Exception $e) {
	            $this->messageManager->addError(__($e->getMessage()));
	        }
        endif;
        return $this->_resultPageFactory->create();
	}
}