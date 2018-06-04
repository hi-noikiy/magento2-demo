<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Bannerslider
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Bannerslider\Block;

/**
 * Bannerslider Block
 * @category Magestore
 * @package  Magestore_Bannerslider
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class Staticblockcontent extends \Magento\Framework\View\Element\Template
{


    //protected $scopeConfig;
    //protected $StoreManagerInterface;


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context
        //\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        //\Magento\Store\Model\StoreManagerInterface $StoreManagerInterface
    ) {
        parent::__construct($context);
        //$this->scopeConfig = $scopeConfig;
        //$this->StoreManagerInterface = $StoreManagerInterface;

    }


    public function getCustomBaseUrl(){

      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
      return $storeManager->getStore()->getBaseUrl();


    }



    public function getImageArrayData() {

     $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
     $mediaUrlbase = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

     $upload_dir = 'static_block_image';

     $aImageUrl = array();

                    for($i = 1 ; $i <= 5 ; $i++){
                        $val_dyna_img = 'bannerslider/static_block_'.$i.'/upload_image_id_'.$i;
                        $conf_image = $this->_scopeConfig->getValue($val_dyna_img, $storeScope);
                        ${"dyna_confimage_" . $i} = $mediaUrlbase.$upload_dir.'/'.$conf_image;
                        $aImageUrl [] = ${"dyna_confimage_" . $i};
                    }

     return $aImageUrl;

     }

    public function getTitleArrayData() {

     $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

     $aTitle = array();

                    for($i = 1 ; $i <= 5 ; $i++){
                        $val_dyna_title = 'bannerslider/static_block_'.$i.'/image_title_'.$i;
                        $conf_title = $this->_scopeConfig->getValue($val_dyna_title, $storeScope);
                        ${"dyna_conftitle_" . $i} = $conf_title;
                        $aTitle [] = ${"dyna_conftitle_" . $i};
                    }

     return $aTitle;

     }

     public function getUrlArrayData() {

     $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

     $aUrl = array();


                    for($i = 1 ; $i <= 5 ; $i++){
                        $val_dyna_url = 'bannerslider/static_block_'.$i.'/image_url_'.$i;
                        $conf_url = $this->_scopeConfig->getValue($val_dyna_url, $storeScope);
                        ${"dyna_confurl_" . $i} = $this->getCustomBaseUrl().$conf_url;
                        $aUrl [] = ${"dyna_confurl_" . $i};
                    }

     return $aUrl;

     }

    public function getDinningCollectionData(){

         $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $mediaUrlbase = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

     $upload_dir = 'static_block_image';

     $aDinningCollectionData = array();

                        $conf_image = $this->_scopeConfig->getValue('bannerslider/static_block_6/upload_image_id_6', $storeScope);
                        $conf_url = $this->_scopeConfig->getValue('bannerslider/static_block_6/image_url_6', $storeScope);
                        $conf_title = $this->_scopeConfig->getValue('bannerslider/static_block_6/image_title_6', $storeScope);
                        $image = $mediaUrlbase.$upload_dir.'/'.$conf_image;
                        $title = $conf_title;
                        $url = $conf_url;
                        $aDinningCollection ['image'] = $image;
                        $aDinningCollection ['url'] = $url;
                        $aDinningCollection ['title'] = $title;


     return $aDinningCollection;



    }
}
