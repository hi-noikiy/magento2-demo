<?php

namespace Iksula\MobileApp\Helper;

class Home extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_filterProvider = $filterProvider;
        $this->_blockFactory = $blockFactory;
    }
    public function getShopByCategoryBlocks(){
        
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $mediaUrlbase = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $upload_dir = 'static_block_image';

        $aImage = [];
        //$aImage['Header'] = "SHOP BY CATEGORIES";

        for($i = 0 ; $i < 6 ; $i++){
            $id = $i+1;
            $val_dyna_img = 'bannerslider/static_block_'.$id.'/upload_icon_image_id_'.$i;
            $conf_image = $this->_scopeConfig->getValue($val_dyna_img, $storeScope);
            ${"dyna_confimage_" . $i} = $mediaUrlbase.$upload_dir.'/'.$conf_image;
            $aImage[$i]['image_url'] = ${"dyna_confimage_" . $i};

            $val_dyna_title = 'bannerslider/static_block_'.$id.'/icon_image_title_'.$i;
            $conf_title = $this->_scopeConfig->getValue($val_dyna_title, $storeScope);
            ${"dyna_conftitle_" . $i} = $conf_title;
            $aImage[$i]['title'] = ${"dyna_conftitle_" . $i};

            $val_dyna_url = 'bannerslider/static_block_'.$id.'/cat_'.$i;
            $conf_url = $this->_scopeConfig->getValue($val_dyna_url, $storeScope);
            ${"dyna_confurl_" . $i} = $conf_url;
            $aImage[$i]['cat_id']= ${"dyna_confurl_" . $i};
        }
        return $aImage;
    }

    public function getBannerBlock(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $mediaUrlbase = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $upload_dir = 'static_block_image';

        $bannerImage = [];
        //$bannerImage['Header'] = "Banner Image";
        $i=0;

        $val_dyna_img = 'bannerslider/banner_block_'.$i.'/upload_banner_image_id_'.$i;
        $conf_image = $this->_scopeConfig->getValue($val_dyna_img, $storeScope);
        ${"dyna_confimage_" . $i} = $mediaUrlbase.$upload_dir.'/'.$conf_image;
        $bannerImage[$i]['image_url'] = ${"dyna_confimage_" . $i};

        $val_dyna_url = 'bannerslider/banner_block_'.$i.'/banner_cat_'.$i;
        $conf_url = $this->_scopeConfig->getValue($val_dyna_url, $storeScope);
        ${"dyna_confurl_" . $i} = $conf_url;
        $bannerImage[$i]['cat_id']= ${"dyna_confurl_" . $i};
        
        $val_dyna_short_title = 'bannerslider/banner_block_'.$i.'/banner_image_short_title_'.$i;
        $conf_short_title = $this->_scopeConfig->getValue($val_dyna_short_title, $storeScope);
        ${"dyna_confshorttitle_" . $i} = $conf_short_title;
        $bannerImage[$i]['shortTitle'] = ${"dyna_confshorttitle_" . $i};
        
        $val_dyna_title = 'bannerslider/banner_block_'.$i.'/banner_image_title_'.$i;
        $conf_title = $this->_scopeConfig->getValue($val_dyna_title, $storeScope);
        ${"dyna_conftitle_" . $i} = $conf_title;
        $bannerImage[$i]['title'] = ${"dyna_conftitle_" . $i};
        
        return $bannerImage;
    }

    public function getFooterBlock(){
        $blockIdentifier = 'footer_links_block';
        $storeId = $this->_storeManager->getStore()->getId();
        /** @var \Magento\Cms\Model\Block $block */
        $block = $this->_blockFactory->create();
        $block->setStoreId($storeId)->load($blockIdentifier);
        $blockId = $block->getData('block_id');
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($blockId) {
          $cs_phone = $this->_scopeConfig->getValue('mobileapp/contact_us/footer_contactno', $storeScope);
          $cs_email = $this->_scopeConfig->getValue('mobileapp/contact_us/footer_email',$storeScope);
          
          $aboutUs = array('About2xl' => '5',
                       'ConsumerRights' => '9', 
                       'PrivacyPolicy' => '4' );
          $customerService = array('ShippingInformation' => '12', 
                          'ReturnsInformation' => '13', 
                          'OrderTracking' => '', 
                          'FAQ\'s' => '8', 
                          'Feedback' => '');                      
          $contactUs = array('phone'=> $cs_phone,
                              'email'=> $cs_email);

          $footerData['Information']['About'] = $aboutUs;
          $footerData['Information']['CustomerService'] = $customerService;
          $footerData['ContactUs'] = $contactUs;
          
          return $footerData;
        }
    }
}
