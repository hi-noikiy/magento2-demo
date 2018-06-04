<?php
namespace Iksula\Ordersplit\Block\Adminhtml\Form\Renderer;

use Magento\Framework\DataObject;


class Picklistdownload extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $ordersplitFactory;
    protected $storemanagerFactory;
    protected $_backendUrl;

    public function __construct(\Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory ,
                                \Iksula\Storemanager\Model\StoremanagerFactory $storemanagerFactory,
                                \Magento\Backend\Model\UrlInterface $backendUrl
                                )
    {

      $this->ordersplitFactory = $ordersplitFactory;
      $this->storemanagerFactory = $storemanagerFactory;
      $this->_backendUrl = $backendUrl;


    }
    public function render(DataObject $row)
    {
      $rowId = $row->getId();

      $ordersplit_row = $this->ordersplitFactory->create()->load($rowId);
      $picklist_sent = $ordersplit_row->getPicklistSent();
      $order_item_status = $ordersplit_row->getOrderItemStatus();
      $Allowed_status = array('store_accepted' , 'store_shipped' , 'store_invoiced');
      $result = "";
      //$storeCat = $this->categoryFactory->create()->load($mageCateId);
      if(in_array($order_item_status , $Allowed_status)){

        $result .= '<div>
            <a href="#" data-order_item_row_id = "'.$rowId.'" data-action_type ="picklist_sendmail" class="picklist_send-link">Mail Send</a>
            |
        </div>';
      }

      if(in_array($order_item_status , $Allowed_status)){

        $params = array('row_id'=> $rowId);

      $url = $this->_backendUrl->getUrl("ordersplit/accept/downloadpicklist/" , $params);
        $result .=  '<div>
            <a href="'.$url.'" >Download</a>
        </div>';

      }
      return $result;
    }
}
