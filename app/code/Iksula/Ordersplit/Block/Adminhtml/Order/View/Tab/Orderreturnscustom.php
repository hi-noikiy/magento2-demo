<?php

namespace Iksula\Ordersplit\Block\Adminhtml\Order\View\Tab;


class Orderreturnscustom extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'order/view/tab/orderreturnsdetails.phtml';

    protected $_urlInterface;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;



    protected $ordersplitFactory;

    protected $storemanagerfactory;


    protected $_orderFactoryData;


    protected $orderreturnsFactory;

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
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Sales\Model\OrderFactory    $orderFactoryData,
        \Iksula\Orderreturns\Model\OrderreturnFactory $orderreturnsFactory
    ) {
        $this->coreRegistry = $registry;
        $this->ordersplitFactory = $ordersplitsFactory;
        $this->storemanagerfactory = $storemanagerfactory;
        $this->_urlInterface = $urlInterface;
        $this->_orderFactoryData = $orderFactoryData;
        $this->orderreturnsFactory = $orderreturnsFactory;
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
        return __('Order Returns Details');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Order Returns Details');
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
        return $this->getUrl('ordersplit/order/orderreturnscustom', ['_current' => true]);
    }




    public function getOrderreturnsByOrderid(){

      $orderreturnsTotalData = array();
      $order_id = $this->getOrder()->getId();
     $order_incrementid = $this->_orderFactoryData->create()->load($order_id)->getIncrementId();

     $orderreturnsDetailsCollection = $this->orderreturnsFactory->create()->getCollection()
                           ->addFieldToFilter('order_id' , array('eq' => $order_incrementid))
                           ->getData();

                           foreach ($orderreturnsDetailsCollection as $key => $orderreturnsDetails) {

                             $customeremail = $this->getCustomerEmailId($orderreturnsDetails['customer_id']);

                               $orderreturnsTotalData []= array('order_id' => $orderreturnsDetails['order_id'] , 'quantity' => $orderreturnsDetails['quantity'] , 'return_reason' => $orderreturnsDetails['return_reason'] , 'product_sku' => $orderreturnsDetails['product_sku'] , 'comment' => $orderreturnsDetails['comment']
                               , 'product_price' => $orderreturnsDetails['product_price'] , 'pickup_time' => $orderreturnsDetails['pickup_time'], 'pickup_date' => $orderreturnsDetails['pickup_date'], 'created_at' => $orderreturnsDetails['created_at']
                             , 'customer_email' => $customeremail);
                           }


                           return $orderreturnsTotalData;


    }


    public function getCustomerEmailId($customer_id){


      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $customerObj = $objectManager->create('Magento\Customer\Model\Customer')
                  ->load($customer_id);

                  return $customerObj->getEmail();

    }


  
}
