<?php
namespace Iksula\Ordersplit\Block\Adminhtml\Form\Renderer;

use Magento\Framework\DataObject;

class Shipmentgettrackingnumber extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $shipment_status = $ordersplit_row->getShipmentStatus();
        $shipment_id = trim($ordersplit_row->getShipmentId());

      if($shipment_id != ""){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $sShipmentid = $objectManager->create('Magento\Sales\Model\Order\Shipment')
              ->getCollection()
              ->addFieldToFilter('increment_id' , array('eq' => $shipment_id))->getData();

              $sShipmentid = $sShipmentid[0]['entity_id'];


        $shipmenttracknumber = $objectManager->create('Magento\Sales\Model\Order\Shipment\Track')
              ->getCollection()
              ->addFieldToFilter('parent_id' , array('eq' => $sShipmentid))->getData();

              $shipmenttracknumber = $shipmenttracknumber[0]['track_number'];

              return $shipmenttracknumber;

        }
    }
}
