<?php
namespace Iksula\Ordersplit\Block\Adminhtml\Form\Renderer;

use Magento\Framework\DataObject;

class OrderItemsData extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    protected $ordersplitFactory;

    public function __construct(\Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory){

        $this->ordersplitFactory = $ordersplitFactory;

    }

    public function render(DataObject $row)
    {
        $rowId = $row->getId();


        $order_items_data = $this->ordersplitFactory->create()->load($rowId)->getOrderItemsData();

        $aOrder_items_data = json_decode($order_items_data , true);


        $Values = "";
        foreach($aOrder_items_data as $OrderItemsDataValues){

            $Values .= "Sku :- ".$OrderItemsDataValues['sku']. " , Qty:- ".round($OrderItemsDataValues['inventory'])."<br />";


        }


        //$storeCat = $this->categoryFactory->create()->load($mageCateId);
        return $Values;
    }
}
