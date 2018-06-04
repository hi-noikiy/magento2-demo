<?php

namespace Iksula\Wishlist\Controller\Index;

class WishlistItem extends \Magento\Framework\App\Action\Action
{
    protected $wishlist;
    protected $customerSession;
    protected $resultPageFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,        
	    \Magento\Wishlist\Model\Wishlist $wishlist,
	    array $data = []
	) {
	    $this->wishlist = $wishlist;
	    $this->resultPageFactory = $resultPageFactory;
	    $this->customerSession = $customerSession;
	    parent::__construct($context, $data);        
	}

	public function execute()
	{
	    $customerId =  $this->customerSession->getCustomer()->getId();
	   // $wishlist_id = $this->getRequest()->getParam('wishlist_id');
        $productId = $this->getRequest()->getParam('product_id');
	    $wish = $this->wishlist->loadByCustomerId($customerId);
	    $items = $wish->getItemCollection();

	    // @var \Magento\Wishlist\Model\Item $item 
	    foreach ($items as $item) {
	        if ($item->getProductId() == $productId) {
	            $item->delete();
	            $wish->save();
	        }
	        /*$resultRedirect = $this->resultPageFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect	->getRefererUrl());
            return $resultRedirect;*/
	    }
	}
}