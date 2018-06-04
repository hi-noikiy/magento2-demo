<?php
namespace Iksula\Ordersplit\Controller\Adminhtml\Shipmentcreation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class  Downloadshipment extends Action
{

    protected $_resultPageFactory;
    protected $ordersplitFactory;
    protected $ordersplitHelper;


    public function __construct(Context $context
                                ,PageFactory $resultPageFactory
                                , \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
                                ,\Iksula\Ordersplit\Helper\Data $ordersplitHelper
                              ) {

        $this->_resultPageFactory = $resultPageFactory;
        $this->ordersplitFactory = $ordersplitFactory;
        $this->ordersplitHelper = $ordersplitHelper;


        parent::__construct($context);
    }

    public function execute()
    {

      $shipment_id = $this->getRequest()->getParam('shipment_id');
      $row_id = $this->getRequest()->getParam('row_id');
      $path = 'pub/media/shipmentsplitorder/';
      $filename = "shipment_".$shipment_id.".pdf";
      $file = $path.$filename;
      if(file_exists($file)){

          if(unlink($file)){

              $status = $this->ordersplitHelper->createPdf($filename  , $path , 'shipment_creation' , $shipment_id , $row_id);
              if(!$status){
                exit('File creating issue');
              }
          }

      }else{

        $status = $this->ordersplitHelper->createPdf($filename  , $path , 'shipment_creation' , $shipment_id , $row_id);

          if(!$status){
            exit('File creating issue');
          }

      }

      header("Content-Disposition: attachment; filename=" . urlencode($filename));
      header("Content-Type: application/octet-stream");
      header("Content-Type: application/download");
      header("Content-Description: File Transfer");
      header("Content-Length: " . filesize($file));
      flush(); // this doesn't really matter.
      $fp = fopen($file, "r");
      while (!feof($fp))
      {
          echo fread($fp, 65536);
          flush(); // this is essential for large downloads
      }
      fclose($fp);
    }
}
