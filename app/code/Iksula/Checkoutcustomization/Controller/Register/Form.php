<?php
/**
*
* Copyright © 2016 Jutecommerce. All rights reserved.
*/
namespace Iksula\Checkoutcustomization\Controller\Register;
use Magento\Framework\Controller\ResultFactory;

class Form extends \Magento\Framework\App\Action\Action{

    /**
    * @var \Magento\Framework\App\Cache\TypeListInterface
    */
    protected $_cacheTypeList;

    /**
    * @var \Magento\Framework\App\Cache\StateInterface
    */
    protected $_cacheState;

    /**
    * @var \Magento\Framework\App\Cache\Frontend\Pool
    */
    protected $_cacheFrontendPool;

    /**
    * @var \Magento\Framework\View\Result\PageFactory
    */
    protected $resultPageFactory;

    /**
    * @param Action\Context $context
    * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    * @param \Magento\Framework\App\Cache\StateInterface $cacheState
    * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
    */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
    parent::__construct($context);
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheState = $cacheState;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
    * Flush cache storage
    *
    */
    public function execute(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$customerSession = $objectManager->get('Magento\Customer\Model\Session');
        if(!$customerSession->isLoggedIn()) {
                $this->_view->loadLayout();
                $this->_view->getLayout()->initMessages();
                $this->_view->getPage()->getConfig()->getTitle()->set(__('2XL Customer Registration'));
                $resultPage = $this->resultPageFactory->create();
                return $resultPage;   
        }else{
             $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
           $resultRedirect->setUrl($this->_redirect->getRefererUrl());
           return $resultRedirect;

        }
    }
}