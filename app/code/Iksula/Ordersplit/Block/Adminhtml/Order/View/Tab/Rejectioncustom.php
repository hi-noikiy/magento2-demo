<?php

namespace Iksula\Ordersplit\Block\Adminhtml\Order\View\Tab;


class Rejectioncustom extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'order/view/tab/rejectionhistory.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;



    protected $rejectionhistory;

    protected $storemanagerfactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Iksula\Ordersplit\Model\RejectionFactory $rejectionfactory,
        \Iksula\Storemanager\Model\StoremanagerFactory $storemanagerfactory
    ) {
        $this->coreRegistry = $registry;
        $this->rejectionfactory = $rejectionfactory;
        $this->storemanagerfactory = $storemanagerfactory;
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
        return __('Rejection History');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Rejection History');
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
        return $this->getUrl('ordersplit/order/rejectionhistory', ['_current' => true]);
    }

    public function getRejectionHistory(){

      $order_id = $this->getOrder()->getId();
      $rejectionhistorycollection = $this->rejectionfactory->create()->getCollection()
                            ->addFieldToFilter('order_id' , array('eq' => $order_id))
                            ->setOrder('id' , 'desc')
                            ->getData();
                            $rejectionhistorydata  = array();
                            $storename = "";
                            foreach ($rejectionhistorycollection as $key => $rejectionhistoryvalues) {

                                $storename = $this->storemanagerfactory->create()->load($rejectionhistoryvalues['rejected_storeid'] , 'storemanager_id')->getStoreName();

                                $rejectionhistorydata []= array('order_unique_id' => $rejectionhistoryvalues['ordersplit_uniqueid'] , 'comments' => $rejectionhistoryvalues['rejection_comment'] , 'store_name' => $storename);
                            }



                            return $rejectionhistorydata;




    }


}
