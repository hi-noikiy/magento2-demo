<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product description block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Iksula\ProductDetailPg\Block\Product\View;

use Magento\Catalog\Model\Product;

class Description extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $_product = null;
    protected $_storeManager;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    protected $_swatch_info = null;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Iksula\ColorSwatch\Model\ProductdetailpgFactory $swatch_info,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_storeManager = $storeManager; 
        $this->_coreRegistry = $registry;
        $this->_swatch_info = $swatch_info;
        parent::__construct($context, $data);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product;
    }

    public function getCollection(){
        return $this->_swatch_info->create()->getCollection();
    }

    public function getImageElement($src)
    {
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return '<img alt="Image" src="'. $mediaUrl . $src . '" width="30px;" height="30px;" />';
    }
}
