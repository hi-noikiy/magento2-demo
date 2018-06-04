<?php

namespace Iksula\Ordersplit\Block\Adminhtml\Order\View\Tab;


class Chequedetails extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'order/view/tab/chequedetails.phtml';

    protected $_urlInterface;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    protected $chequedetails = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Iksula\Checkoutcustomization\Model\Chequedetails $chequedetails,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->coreRegistry = $registry;
        $this->_urlInterface = $urlInterface;
        $this->chequedetails = $chequedetails;
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
        return __('Cheque Details');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Cheque Details');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        // For me, I wanted this tab to always show
        // You can play around with the ACL settings
        // to selectively show later if you want
        $paymentCode = $this->getOrder()->getPayment()->getMethodInstance()->getCode();
        if($paymentCode != 'checkmo'){
            return false;
        }else{
            return true;
        }
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
        return $this->getUrl('ordersplit/order/chequedetails', ['_current' => true]);
    }

    public function getChequeDetails(){
        $order_id = $this->getOrder()->getId();
        $chequedetails = $this->chequedetails->getCollection()
                            ->addFieldToFilter('order_id' , array('eq' => $order_id))
                            ->setOrder('id' , 'desc')
                            ->getData();
        return $chequedetails;
    }
}
