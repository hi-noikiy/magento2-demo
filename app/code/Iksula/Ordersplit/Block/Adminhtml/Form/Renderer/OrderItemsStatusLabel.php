<?php
namespace Iksula\Ordersplit\Block\Adminhtml\Form\Renderer;

use Magento\Framework\DataObject;

class OrderItemsStatusLabel extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    protected $ordersplitFactory;
    protected $orderitemstatusFactory;

    public function __construct(\Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory,
                                \Iksula\Orderitemstatus\Model\OrderitemstatusFactory $orderitemstatusFactory){

        $this->ordersplitFactory = $ordersplitFactory;
        $this->orderitemstatusFactory = $orderitemstatusFactory;

    }

    public function render(DataObject $row)
    {
        $rowId = $row->getId();


        $order_items_statuscode = $this->ordersplitFactory->create()->load($rowId)->getOrderItemStatus();

        $order_items_statuslabel = $this->orderitemstatusFactory->create()->load( $order_items_statuscode , 'code')->getName();

        if($order_items_statuslabel == ""){
            $order_items_statuslabel = $order_items_statuscode;
        }
        return $order_items_statuslabel;
    }
}
