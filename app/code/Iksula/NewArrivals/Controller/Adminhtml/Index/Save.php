<?php
namespace Iksula\NewArrivals\Controller\Adminhtml\Index;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
class Save extends \Magento\Backend\App\Action
{
    protected $helper;
    /**
     * @param Action\Context $context
     */
    public function __construct(
        \Iksula\NewArrivals\Helper\Data $helper,
        Action\Context $context
    ){
        
        $this->helper = $helper;
        parent::__construct($context);
    }
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try{
            $this->helper->addNewArrivalProducts();
            $this->messageManager->addSuccess(__('Products assigned successfully.'));
        }catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while adding products.'));
        }
        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
    }
}