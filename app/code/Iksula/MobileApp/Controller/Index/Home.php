<?php
namespace Iksula\MobileApp\Controller\Index;

use Magento\Framework\App\Action\Context;

class Home extends \Magento\Framework\App\Action\Action
{
    const REQUEST_SCHEME = 'REQUEST_SCHEME';
    const REQUEST_URI = 'REQUEST_URI';
    const REQUEST_METHOD = 'REQUEST_METHOD';
    const SERVER_NAME = 'SERVER_NAME';
    const HTTP_AUTHORIZATION = 'HTTP_AUTHORIZATION';
    protected $_request;
    protected $_resultFactory;
    protected $_result = array();
    private $dataHelper;
    /**
     * Index constructor.
     * @param Context $context
     * @param \Magento\Framework\App\Http $httpRequest
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Iksula\MobileApp\Helper\Data $helperData
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Http $httpRequest,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Iksula\MobileApp\Helper\Data $helperData,
        \Iksula\MobileApp\Helper\Home $helper
    )
    {
        $this->_request = $httpRequest;
        $this->_resultFactory = $resultRawFactory;
        $this->dataHelper = $helperData;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {   //echo "shweta";exit;
/*        $authorization = $this->_request->getServer(self::HTTP_AUTHORIZATION);
        $method = $this->_request->getServer(self::REQUEST_METHOD);*/
        $result['HomepageDecorBlocks'] = $this->dataHelper->getHomePageDecorBlock();
        $result['BannerData'] = $this->dataHelper->getBannerData();
        $result['Category']            = $this->dataHelper->getAllCategories();
        $result['ShopByCategoryBlocks'] = $this->helper->getShopByCategoryBlocks();
        $result['BannerBlock'] = $this->helper->getBannerBlock();
        $result['FooterBlock'] = $this->helper->getFooterBlock();
        $result['responseCode'] = "200";
        $result['success'] =  "true";
        
        $response = $this->_resultFactory->create();
        $response->setContents(json_encode($result, true));
        return $response;
               
    }

}
