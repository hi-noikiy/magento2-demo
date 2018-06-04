<?php
namespace Iksula\Ordersplit\Block\Adminhtml\Form\Renderer;

use Magento\Framework\DataObject;

class Acceptreject extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{


  protected $ordersplitFactory;

  public function __construct(
      \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
  ) {
      $this->ordersplitFactory = $ordersplitFactory;
  }


    public function render(DataObject $row)
    {
        // $ExcludeOrderItemsStatus = array('store_accepted' , 'store_rejected' , 'store_unallocated', 'store_invoiced' , 'store_shipped', 'store_cancelled');


        $IncludeOrderItemsStatus = array('store_allocated');
        $rowId = $row->getId();
        //$storeCat = $this->categoryFactory->create()->load($mageCateId);
        $order_item_status = $this->ordersplitFactory->create()->load($rowId)->getOrderItemStatus();

        //$storeCat = $this->categoryFactory->create()->load($mageCateId);
        if(in_array($order_item_status , $IncludeOrderItemsStatus)){
        return '<div>
    				<a href="#" data-order_item_row_id = "'.$rowId.'" data-action_type ="accept" class="accept-link">Accept</a> |
                    <a href="#" data-order_item_row_id = "'.$rowId.'" data-action_type ="reject" class="reject-link">Reject</a>
				</div>';
      }
    }
}
