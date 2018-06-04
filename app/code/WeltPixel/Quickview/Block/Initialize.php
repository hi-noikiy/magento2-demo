<?php
namespace WeltPixel\Quickview\Block;

/**
 * Quickview Initialize block
 */
class Initialize extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \WeltPixel\QuickView\Helper\Data
     */
    protected $_helper;

    const XML_PATH_QUICKVIEW_ENABLED = 'weltpixel_quickview/general/enable_product_listing';
    const XML_PATH_QUICKVIEW_BUTTONSTYLE = 'weltpixel_quickview/general/button_style';

    /**
     * @param \WeltPixel\Quickview\Helper\Data $helper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(\WeltPixel\Quickview\Helper\Data $helper,
                                \Magento\Framework\View\Element\Template\Context $context,
                                \Magento\Framework\UrlInterface $urlInterface,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                                \Magento\Framework\App\Request\Http $request,
                                array $data = [])
    {
        $this->_helper = $helper;
         $this->urlInterface = $urlInterface;
        $this->scopeConfig = $scopeConfig;
        $this->_request = $request;
        parent::__construct($context, $data);
    }

    /**
     * Returns config
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'baseUrl' => $this->getBaseUrl(),
            'closeSeconds' => $this->_helper->getCloseSeconds(),
            'showMiniCart' => $this->_helper->getScrollAndOpenMiniCart(),
            'showShoppingCheckoutButtons' => $this->_helper->getShoppingCheckoutButtons()
        ];
    }

    /**
     * Return base url.
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Return quickview button.
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getQuickViewButton(\Magento\Catalog\Model\Product $product)
    {
        $result = '';
        $isEnabled = $this->scopeConfig->getValue(self::XML_PATH_QUICKVIEW_ENABLED,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($isEnabled) {
            $buttonStyle =  'weltpixel_quickview_button_' . $this->scopeConfig->getValue(self::XML_PATH_QUICKVIEW_BUTTONSTYLE,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $productUrl = $this->urlInterface->getUrl('weltpixel_quickview/catalog_product/view', array('id' => $product->getId()));
            return $result . '<a class="weltpixel-quickview '.$buttonStyle.'" data-quickview-url=' . $productUrl . ' href="javascript:void(0);"><span>' . __("Quickview") . '</span></a>';
        }
        
        return $result;
    }

    /**
     * Return action name.
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getActionName()
    {   
        return $this->_request->getFullActionName();
    }
}
