<?php
namespace Iksula\SalecategoryProducts\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;


class Save extends \Magento\Backend\App\Action
{
	protected $helper;
    /**
     * @param Action\Context $context
     */
    public function __construct(
		\Iksula\SalecategoryProducts\Helper\Data $helper,
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
            $this->helper->addAllDiscountedProductsToSaleCategory();
            $this->messageManager->addSuccess(__('The Products are added successfully.'));

        }catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while adding products.'));
        }
		return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
    }
}