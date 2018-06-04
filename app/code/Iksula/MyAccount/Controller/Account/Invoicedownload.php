<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksula\MyAccount\Controller\Account;

/**
 * Class EditPost
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Invoicedownload extends \Magento\Framework\App\Action\Action
{

    protected $orderFactoryData;
    protected $session;
    protected $baseurl;
    protected $orderHelperData;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\Redirect $resultRedirectFactory,
        \Magento\Customer\Model\Session $session,
        \Magento\Sales\Model\OrderFactory $orderFactoryData,
        \Magento\Store\Model\StoreManagerInterface $baseurl,
        \Iksula\Ordersplit\Helper\Data $orderHelperData
    )
    {

        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->orderFactoryData = $orderFactoryData;
        $this->session = $session;
        $this->baseurl = $baseurl;
        $this->orderHelperData = $orderHelperData;

        // $this->_url = $url;
        // $this->_messageManager = $messageManager;
        parent::__construct($context);

    }

    public function getLogoSrc(){
          return $this->baseurl->getStore()->getBaseUrl().'pub/static/frontend/twoxl/twoxl/en_US/images/logo.jpg';
    }



public function authenticatecustomer($order_id){

    if($this->isCustomerLoggedIn()){

        $customer_id = $this->getCustomerId();
        $Order_Obj = $this->orderFactoryData->create()->load($order_id);
        $OrderCustomerid = $Order_Obj->getCustomerId();
        if($OrderCustomerid == $customer_id){
            $this->createInvoice($order_id);
        }
    }
}


public function execute(){


    $order_id = $this->getRequest()->getParam('order_id');

  $this->authenticatecustomer($order_id);
}


public function isCustomerLoggedIn()
{
    $session = $this->session;
    return $session->isLoggedIn();
}

public function getCustomerId(){
    $session = $this->session;
    return $session->getCustomer()->getId();
}


public function createInvoice($order_id){

          $orderincrementid = $this->orderFactoryData->create()->load($order_id)->getIncrementId();

          $path = 'pub/media/invoiceforcustomer/';
          $filename = "invoice_".$orderincrementid.".pdf";
          $file = $path.$filename;


          if(file_exists($file)){

              if(unlink($file)){

                  $status = $this->orderHelperData->createPdf($filename  , $path , 'customer_invoice' ,  $order_id , '');
                  if(!$status){
                    exit('File creating issue');
                  }
              }

          }else{

            $status = $this->orderHelperData->createPdf($filename  , $path , 'customer_invoice' ,  $order_id , '');

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
