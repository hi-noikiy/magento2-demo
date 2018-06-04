<?php
namespace Iksula\Ordersplit\Controller\Adminhtml\Reject;
 
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
 
class  Template extends Action
{
    
    protected $_resultPageFactory;
 
    
    public function __construct(Context $context,PageFactory $resultPageFactory) {

        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    } 

    public function execute()
    {
       $resultPage = $this->_resultPageFactory->create();
       /*$resultPage->addHandle('ordersplit_manualallocation_template'); 
       return $resultPage;*/
       $block = $resultPage->getLayout()
                ->createBlock('Iksula\Ordersplit\Block\Adminhtml\Ordersplits\Reject')
                ->setTemplate('Iksula_Ordersplit::ordersplits/reject.phtml')
                ->toHtml();
        $this->getResponse()->setBody($block);
    }
}