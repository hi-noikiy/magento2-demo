<?php
namespace Iksula\Ordersplit\Controller\Adminhtml\Invoicecreation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class  Downloadinvoice extends Action
{

    protected $_resultPageFactory;


    public function __construct(Context $context,PageFactory $resultPageFactory
                              ) {

        $this->_resultPageFactory = $resultPageFactory;

        parent::__construct($context);
    }

    public function execute()
    {


      $invoice_id = $this->getRequest()->getParam('invoice_id');

      header("Content-Type: application/octet-stream");

      $file = $invoice_id.".pdf";
      header("Content-Disposition: attachment; filename=" . urlencode($file));
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
