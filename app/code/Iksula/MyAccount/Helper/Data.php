<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
    namespace Iksula\MyAccount\Helper;

    class Data extends \Magento\Framework\App\Helper\AbstractHelper
    {
        protected $_country_carrierCollection;

        public function __construct( \Iksula\Carriercodetelephone\Model\CarriercodedataFactory $country_carrierCollection, array $data = [])
        {
          $this->_country_carrierCollection = $country_carrierCollection;
        }

        public function getCountryCarrierCollection()
        {
            $collection = $this->_country_carrierCollection->create()->getCollection();
            return $collection->getData();
        }
    }