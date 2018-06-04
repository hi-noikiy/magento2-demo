<?php

namespace Iksula\Ordersplit\Block\Adminhtml\Order\View\Tab;


class Allocationdetails extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'order/view/tab/allocationdetails.phtml';

    protected $_urlInterface;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;



    protected $ordersplitFactory;

    protected $storemanagerfactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitsFactory,
        \Iksula\Storemanager\Model\StoremanagerFactory $storemanagerfactory,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->coreRegistry = $registry;
        $this->ordersplitFactory = $ordersplitsFactory;
        $this->storemanagerfactory = $storemanagerfactory;
        $this->_urlInterface = $urlInterface;
        parent::__construct($context);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Allocation Details');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Allocation Details');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        // For me, I wanted this tab to always show
        // You can play around with the ACL settings
        // to selectively show later if you want
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        // For me, I wanted this tab to always show
        // You can play around with conditions to
        // show the tab later
        return false;
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        // I wanted mine to load via AJAX when it's selected
        // That's what this does
        return 'ajax only';
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * Get Tab Url
     *
     * @return string
     */
    public function getTabUrl()
    {
        // customtab is a adminhtml router we're about to define
        // the full route can really be whatever you want
        return $this->getUrl('ordersplit/order/allocationdetails', ['_current' => true]);
    }


    public function getAllocationDetailsForOrderIds(){

      $order_id = $this->getOrder()->getId();
      $allocationsdetailscollection = $this->ordersplitFactory->create()->getCollection()
                            ->addFieldToFilter('order_id' , array('eq' => $order_id))
                            ->setOrder('id' , 'desc')
                            ->getData();
                            $allocationsdetails  = array();
                            $storename = "";
                            $allocationsdetailsData = array();
                            foreach ($allocationsdetailscollection as $key => $orderdetailsvalues) {

                                $storecode = $this->storemanagerfactory->create()->load($orderdetailsvalues['allocated_storeids'] , 'storemanager_id')->getStoreCode();

                                $order_items_data = json_decode($orderdetailsvalues['order_items_data'] , true);
                                $sOrderItemsData = '';
                                foreach($order_items_data as $vOrderItemsData){
                                    $sOrderItemsData .= 'Sku :- '.$vOrderItemsData['sku'].', Qty :- '.round($vOrderItemsData['inventory'])."<br />";
                                }

                                $allocationsdetailsData []= array('order_item_id' => $orderdetailsvalues['order_item_id'] , 'order_item_data' => $sOrderItemsData , 'order_item_status' => $orderdetailsvalues['order_item_status'] , 'store_code' => $storecode , 'ordersplit_id' => $orderdetailsvalues['id']);
                            }
                            return $allocationsdetailsData;

    }


    public function getCancelOrderItemsUrl(){

      return $this->_urlInterface->getUrl('ordersplit/cancelorderitem/index');

    }


    public function checkIfOrderItemCancelled($row_id){


        $order_item_status = $this->ordersplitFactory->create()->load($row_id)->getOrderItemStatus();
        if($order_item_status == 'store_cancelled'){
            return true;
        }else{
          return false;
        }

    }



}
