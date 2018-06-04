<?php

namespace Iksula\Ordersplit\Block\Adminhtml\Ordersplits;

class Createshipment extends \Magento\Backend\Block\Template
{

    protected $ordersplitFactory;
    protected $storeinventoryFactory;
    protected $storemanagerFactory;
    protected $OrderSplitHelper;

    public function __construct( \Magento\Backend\Block\Template\Context $context
                                ,  \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
                                , \Iksula\Storeinventory\Model\StoreinventoryFactory $storeinventoryFactory
                                , \Iksula\Storemanager\Model\StoremanagerFactory $storemanagerFactory
                                , \Iksula\Ordersplit\Helper\Data   $OrderSplitHelper
                                ){


        $this->ordersplitFactory = $ordersplitFactory;
        $this->storeinventoryFactory = $storeinventoryFactory;
        $this->storemanagerFactory = $storemanagerFactory;
        $this->OrderSplitHelper = $OrderSplitHelper;
         parent::__construct($context);

    }

    public function getOrderItemRowId(){

        $order_itemsids = $this->getRequest()->getParam('order_itemrowid');

        return $order_itemsids;

    }

}
