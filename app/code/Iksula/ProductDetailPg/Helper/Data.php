<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
    namespace Iksula\ProductDetailPg\Helper;

    class Data extends \Magento\Framework\App\Helper\AbstractHelper
    {
        protected $scopeConfig;

        public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
        {
          $this->scopeConfig = $scopeConfig;
        }

        public function getConfig($config_path)
        {
            return $this->scopeConfig->getValue(
                    $config_path,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
        }

        //public function DisplayDiscountLabel($_product)
        public function DisplayDiscountLabel($_product)
        {
            $originalPrice = $_product->getPrice();
            $finalPrice = $_product->getSpecialPrice();

            $percentage = 0;
            if ($originalPrice > $finalPrice) {
                $percentage = round(($originalPrice - $finalPrice) * 100 / $originalPrice);
            }

            if ($percentage) {
                return "(".$percentage."%)";
            }

        }
    }
