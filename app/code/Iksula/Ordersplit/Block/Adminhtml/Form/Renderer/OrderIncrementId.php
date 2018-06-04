<?php
namespace Iksula\Ordersplit\Block\Adminhtml\Form\Renderer;

use Magento\Framework\DataObject;

class OrderIncrementId extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    protected $orderFactory;
    protected $ordersplitFactory;

    public function __construct(\Magento\Sales\Model\OrderFactory $orderFactory,
                                \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
                              ){

        $this->orderFactory = $orderFactory;
        $this->ordersplitFactory = $ordersplitFactory;

    }

    public function render(DataObject $row)
    {
        $rowId = $row->getId();


        $order_id = $this->ordersplitFactory->create()->load($rowId)->getOrderId();
        $orderincrementid = $this->orderFactory->create()->load($order_id)->getIncrementId();

        $orderincrementid = 'Order #'.$orderincrementid;
        return $orderincrementid;
    }
}
