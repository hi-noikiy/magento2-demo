<?php
namespace Iksula\SalecategoryProducts\Controller\Index;
use \Magento\Framework\Controller\ResultFactory;
class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultFactory;
	protected $helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Iksula\SalecategoryProducts\Helper\Data $helper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
            $this->resultFactory = $resultFactory;
            $this->helper = $helper;
            $this->resultPageFactory = $resultPageFactory;
            parent::__construct($context);
    }
    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->helper->addAllDiscountedProductsToSaleCategory();
    }

}
