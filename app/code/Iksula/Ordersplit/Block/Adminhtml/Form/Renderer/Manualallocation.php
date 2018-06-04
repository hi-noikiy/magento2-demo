<?php
namespace Iksula\Ordersplit\Block\Adminhtml\Form\Renderer;

use Magento\Framework\DataObject;

class Manualallocation extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $ordersplitFactory;

    public function __construct(
        \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
    ) {
        $this->ordersplitFactory = $ordersplitFactory;
    }


    public function render(DataObject $row)
    {
        $rowId = $row->getId();

        $aDontShowforOrderItemsStatus = array('store_accepted' , 'delivered' , 'store_invoiced' , 'store_shipped');

        $order_item_status = $this->ordersplitFactory->create()->load($rowId)->getOrderItemStatus();

        //$storeCat = $this->categoryFactory->create()->load($mageCateId);
        if(!in_array($order_item_status , $aDontShowforOrderItemsStatus)){
          return '<div>
              <a href="#" data-order_item_row_id = "'.$rowId.'" data-action_type ="manual_allocation" class="manual-allocation-link">Manual Allocation</a>
          </div>';
        }
    }
}
