<?php
namespace Iksula\Ordersplit\Block\Adminhtml\Form\Renderer;

use Magento\Framework\DataObject;

class Masterinvoicecreation extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $ordersplitFactory;
    protected $orderFactory;
    protected $_backendUrl;

    public function __construct(
        \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
        ,\Magento\Sales\Model\OrderFactory $orderFactory
        ,\Magento\Backend\Model\UrlInterface $backendUrl
    ) {
        $this->ordersplitFactory = $ordersplitFactory;
        $this->orderFactory = $orderFactory;
        $this->_backendUrl = $backendUrl;
    }


    public function render(DataObject $row)
    {
        $rowId = $row->getId();

        $ordersplitobj = $this->ordersplitFactory->create()->load($rowId);
        $order_item_status = $ordersplitobj->getOrderItemStatus();
        $invoiced_status = $ordersplitobj->getInvoicedStatus();
        $order_id = $ordersplitobj->getOrderId();
        $order_incrementid = $this->orderFactory->create()->load($order_id)->getIncrementId();
        $Allowed_status = array('store_accepted' , 'store_shipped' , 'store_invoiced');

        //$storeCat = $this->categoryFactory->create()->load($mageCateId);
        if(in_array($order_item_status , $Allowed_status)  &&  $invoiced_status == 1){
          $params = array('order_id'=> $order_id , 'row_id' => $rowId , 'order_incrementid' => $order_incrementid);

        $url = $this->_backendUrl->getUrl("ordersplit/accept/downloadmasterinvoice/", $params);
          //$url = $this->getUrl('ordersplit/invoicecreation/template/');
          return '<div>
              <a href="'.$url.'" >Master Invoice Download</a>
          </div>';

        }
    }
}
