<?php
namespace Iksula\Ordersplit\Block\Adminhtml\Form\Renderer;

use Magento\Framework\DataObject;

class Invoiceordersplit extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $ordersplitFactory;
    protected $_backendUrl;

    public function __construct(
        \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory,
         \Magento\Backend\Model\UrlInterface $backendUrl
    ) {
      $this->_backendUrl = $backendUrl;
        $this->ordersplitFactory = $ordersplitFactory;
    }


    public function render(DataObject $row)
    {
        $rowId = $row->getId();

        $ordersplit_row = $this->ordersplitFactory->create()->load($rowId);
        $order_item_status = $ordersplit_row->getOrderItemStatus();
        $invoiced_status = $ordersplit_row->getInvoicedStatus();
        $invoice_id = $ordersplit_row->getInvoiceId();
        $Allowed_status = array('store_accepted' , 'store_shipped' , 'store_invoiced');




        //$storeCat = $this->categoryFactory->create()->load($mageCateId);
      if(in_array($order_item_status , $Allowed_status) && $invoiced_status == 0 ){
          return '<div>
              <a href="#" data-order_item_row_id = "'.$rowId.'" data-action_type ="invoicecreate" class="invoicecreate-link">Invoice</a>
          </div>';
        }elseif($invoiced_status == 1  && !is_null($invoice_id) ){

          $params = array('invoice_id'=> $invoice_id , 'row_id' => $rowId);

        $url = $this->_backendUrl->getUrl("ordersplit/accept/downloadinvoice/", $params);
          //$url = $this->getUrl('ordersplit/invoicecreation/template/');
          return '<div>
              <a href="'.$url.'" >Download</a>
          </div>';

        }
    }
}
