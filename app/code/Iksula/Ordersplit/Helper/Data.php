<?php

namespace Iksula\Ordersplit\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;
use TCPDF_TCPDF;

class Data extends AbstractHelper
{

        protected $orderFactoryData;
        protected $ordersplitFactory;
        protected $orderitemFactoryData;
        protected $rejectionFactory;
        protected $storeinventoryFactory;
        protected $storemanagerhelper;
        protected $storemanagerFactory;
        protected $directoryListinterface;
        protected $baseurl;
        protected $invoiceFactory;
        protected $addressFactory;
        protected $InvoiceItemfactory;
        protected $productfactory;
        protected $storescopeInterface;
        protected $emailidshelper;
        protected $shipmentFactory;
        protected $shipmentItemFactory;
        protected $shippmentTrackfactory;
        protected $localeCurrency;
        protected $modelStoreManagerInterface;
        protected $scopeConfig;

        public function __construct( \Magento\Sales\Model\OrderFactory    $orderFactoryData
                                     , \Magento\Sales\Model\Order\ItemFactory $orderitemFactoryData
                                    , \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
                                    , \Iksula\Ordersplit\Model\RejectionFactory $rejectionFactory
                                    ,\Iksula\Storeinventory\Model\StoreinventoryFactory $storeinventoryFactory
                                    ,\Iksula\Storemanager\Helper\Data $storemanagerhelper
                                    ,\Iksula\Storemanager\Model\StoremanagerFactory $storemanagerFactory
                                    ,\Magento\Framework\App\Filesystem\DirectoryList $DirectoryListInterface
                                    ,\Magento\Store\Model\StoreManagerInterface $baseurl
                                    ,\Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory
                                    ,\Magento\Sales\Model\Order\AddressFactory $addressFactory
                                    ,\Magento\Sales\Model\Order\Invoice\ItemFactory $InvoiceItemfactory
                                    ,\Magento\Catalog\Model\ProductFactory $productfactory
                                    ,\Magento\Store\Model\StoreManagerInterface $storescopeInterface
                                    ,\Iksula\EmailTemplate\Helper\Email $emailidshelper
                                    ,\Magento\Sales\Model\Order\ShipmentRepository $shipmentFactory
                                    ,\Magento\Sales\Model\Order\Shipment\ItemFactory $shipmentItemFactory
                                    ,\Magento\Sales\Model\Order\Shipment\TrackFactory $shippmentTrackfactory
                                    ,\Magento\Store\Model\StoreManagerInterface $modelStoreManagerInterface
                                    ,\Magento\Framework\Locale\CurrencyInterface $localeCurrency
                                    , \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
                                    ){

            $this->orderFactoryData = $orderFactoryData;
            $this->ordersplitFactory = $ordersplitFactory;
            $this->orderitemFactoryData = $orderitemFactoryData;
            $this->rejectionFactory = $rejectionFactory;
            $this->storeinventoryFactory = $storeinventoryFactory;
            $this->storemanagerhelper = $storemanagerhelper;
            $this->storemanagerFactory = $storemanagerFactory;
            $this->directoryListinterface = $DirectoryListInterface;
            $this->baseurl = $baseurl;
            $this->invoiceFactory = $invoiceFactory;
            $this->addressFactory = $addressFactory;
            $this->InvoiceItemfactory = $InvoiceItemfactory;
            $this->productfactory = $productfactory;
            $this->storescopeInterface = $storescopeInterface;
            $this->emailidshelper = $emailidshelper;
            $this->shipmentFactory = $shipmentFactory;
            $this->shipmentItemFactory = $shipmentItemFactory;
            $this->shippmentTrackfactory = $shippmentTrackfactory;
            $this->localeCurrency = $localeCurrency;
            $this->modelStoreManagerInterface = $modelStoreManagerInterface;
            $this->scopeConfig = $scopeConfig;

        }

        public function getLogoSrc(){
              return $this->baseurl->getStore()->getBaseUrl().'pub/static/frontend/twoxl/twoxl/en_US/images/logo.jpg';
        }



        public function getStoreCurrenycode(){
          $currencyCode = $this->modelStoreManagerInterface->getStore()->getBaseCurrencyCode();
          $currencySymbol = $this->localeCurrency->getCurrency($currencyCode)->getSymbol();
          return $currencySymbol;
        }

       public function splitOrderInOrderSplitTable($OrderitemsData , $allocatedStoreId , $action_status ){


                foreach($OrderitemsData as $aOrderItemsValues){

                        $order_item_id = $aOrderItemsValues['order_items_id'];
                        break;
                }



                    $aOrderItemData  = $this->orderitemFactoryData->create()->load($order_item_id);
                    $order_id = $aOrderItemData->getOrderId();
                    $Order_items_id_unique = 'Orderitems';
                    $OrderItemsIncrementId = $this->getLastIncrementIdOfOrderItems($order_id) + 1;
                    $Order_items_id_unique = $Order_items_id_unique .'-'. $order_id .'-'. $OrderItemsIncrementId;


                    $SOrderitemsData = json_encode($OrderitemsData);
                    $store_code = "";

                    if($action_status == 'accept_action'){

                        if( trim($allocatedStoreId) != ""){

                            $order_item_status = 'store_accepted';


                            $store_code = $this->storemanagerhelper->getStoreCodeByStoreId($allocatedStoreId);

                            list($error_status , $message) = $this->deductInventoryIfAccept($OrderitemsData , $store_code);

                            if($error_status == 1){

                              $result ['error'] = 1;
                              $result ['result_content'] = $message;
                              exit;
                            }
                        }else{
                            $order_item_status = 'store_unallocated';
                        }
                    }elseif($action_status == 'manualallocation_action'){

                        if( trim($allocatedStoreId) != ""){

                            $order_item_status = 'store_allocated';


                        }else{
                            $order_item_status = 'store_unallocated';
                        }

                    }elseif($action_status == 'lessinventory_accepted_store'){

                      if( trim($allocatedStoreId) != ""){

                          $order_item_status = 'store_allocated';



                      }else{
                          $order_item_status = 'store_unallocated';
                      }

                    }

                    try{

                    $ordersplitModel = $this->ordersplitFactory->create();
                    $ordersplitModel->setOrderId($order_id);
                    $ordersplitModel->setOrderItemsData($SOrderitemsData);
                    $ordersplitModel->setOrderItemId($Order_items_id_unique);
                    $ordersplitModel->setOrderItemStatus($order_item_status);
                    $ordersplitModel->setAllocatedStoreids($allocatedStoreId);
                    $ordersplitModel->save();

                    $insertnewid = $ordersplitModel->getId(); // Getting the Ordersplit id after new data inserted in Ordersplit table.



                    if($action_status == 'manualallocation_action'){ // This condition is used for sending Email and sms to respective members for manual allocation of orders items

                      /********  Send the Email using helper by jesni *************/
                      $allocated_storecode = $this->storemanagerhelper->getStoreCodeByStoreId($allocatedStoreId);
                      $order_incremenid = $this->orderFactoryData->create()->load($order_id)->getIncrementId();

                      $storeobj = $this->storemanagerhelper->getStoreManagerObject($allocatedStoreId);
                      $store_name = $storeobj->getStoreName();
                      $store_code = $storeobj->getStoreCode();
                      $store_emailid = $storeobj->getStoreEmailid();
                      $EmailTemplatesData = array();


                            $EmailTemplatesData = array('row_id' => $insertnewid , 'order_id' => $order_incremenid);


                      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                      $scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');

                      $domain_name =  $scopeConfig->getValue('sms_configuration/sms_setting/domain_name');
                      $domain_email_id =  $scopeConfig->getValue('sms_configuration/sms_setting/domain_email_id');
                      /* Sender Detail  */

                      if(!is_null($domain_name) && !is_null($domain_email_id) && !is_null($store_name) && !is_null($store_emailid)){
                        $senderInfo = [
                          'name' => $domain_name,
                          'email' => $domain_email_id,
                        ];

                        $reciverInfo = [
                          'name' => $store_name,
                          'email' => $store_emailid,
                        ];
                        $this->emailidshelper->emailTemplate('order_allocation' , $EmailTemplatesData , $senderInfo , $reciverInfo, '' , '');

                        $this->emailidshelper->emailTemplate('order_allocation_admin' , $EmailTemplatesData , $senderInfo , $senderInfo, '' , '');
                      }


                      /*******************************/

                      $is_enable =  $this->scopeConfig->getValue('sms_configuration/sms_setting/enable_allocation');
                      $template_path =  $this->scopeConfig->getValue('sms_configuration/sms_setting/order_allocation');
                      $order_incrementid = $this->orderFactoryData->create()->load($order_id)->getIncrementId();

                      $data = array(
                      'order_id' => $order_incrementid,
                      'store_name' => $store_name,
                      'store_code' => $store_code,
                      'ordersplit_id' => $Order_items_id_unique
                      );

                      $AdminNumber  = $this->scopeConfig->getValue('sms_configuration/sms_setting/admin_number');
                      $storeNumber = $storeobj->getStoreMobileno();
                      $aNumber = array($AdminNumber , $storeNumber);

                      if($is_enable)
                          $this->emailidshelper->smsTemplate($template_path, $data, $aNumber);
                    }




                  }
                  catch(Exception $e){

                      echo $e->getMessage();
                  }

                  if($action_status == 'accept_action'){  // Send the Picklist to warehouse when the store accept the order items

                      $ordersplitmodellatestid = $ordersplitModel->getId();

                        $picklist_mail_status = $this->sendPicklistToWarehouse($store_code, $Order_items_id_unique , $ordersplitmodellatestid , $order_id);

                        if($picklist_mail_status){
                          $ordersplitModelforPicklistUpdate = $this->ordersplitFactory->create()->load($ordersplitmodellatestid);
                          $ordersplitModelforPicklistUpdate->setPicklistSent(1);
                          $ordersplitModelforPicklistUpdate->save();
                        }


                  }

                    $ordersplitstatusObj = $this->orderFactoryData->create()->load($order_id);
                    $ordersplitstatusObj->setOrdersplitStatus(1);
                    $ordersplitstatusObj->save();

        }


        public function getStoreIdsRejected($order_unique_ids){

                $rejectionCollection = $this->rejectionFactory->create()->getCollection()->addFieldToFilter('ordersplit_uniqueid' , array('eq' => $order_unique_ids))->getData();
                $storeids = array();
                if(!empty($rejectionCollection)){
                    foreach($rejectionCollection as $rejectionData){
                        $storeids[] = $rejectionData['rejected_storeid'];
                    }
                }
                array_unique($storeids);

                return $storeids;

        }


        public function getLastIncrementIdOfOrderItems($order_id){
                $OrderitemsData = array();

                $OrderItemsSplitCollection = $this->ordersplitFactory->create()->getCollection()->addFieldToFilter('order_id' , array('eq' => $order_id))->getData();

                if(!empty($OrderItemsSplitCollection)){
                    foreach($OrderItemsSplitCollection as $OrderItemsValues){
                        $OrderitemsData = explode('-' , $OrderItemsValues['order_item_id']);
                        $OrderItemsNumbers []= (int) end ($OrderitemsData);

                    }
                    rsort($OrderItemsNumbers);

                    return current($OrderItemsNumbers) ;
                }else{

                    return 0;

                }

        }


        public function deductInventoryIfAccept($OrderitemsValues , $store_code){

              $inventory = 0 ;
              $storeinventory_id = "";

            foreach($OrderitemsValues as $ItemsValues){

                $sku = $ItemsValues['sku'];
                $Orderqty = $ItemsValues['inventory'];

                $storeinventoryCollection = $this->storeinventoryFactory->create()
                                   ->getCollection()
                                 ->addFieldToFilter('store_id', array('eq' , $store_code))
                               ->addFieldToFilter('sku' , array('eq' , $sku))
                                 ->getData();

                                foreach($storeinventoryCollection as $storeinventoryValues){
                                  $storeinventory_id = $storeinventoryValues['id'];
                                    $inventory = $storeinventoryValues['inventory'];
                                }

                                if($inventory >= $Orderqty){

                                  $remainedqtyInStore = ($inventory - $Orderqty);

                                }

                                $this->storeinventoryFactory->create()
                                                          ->load($storeinventory_id)
                                                          ->setInventory($remainedqtyInStore)
                                                          ->save();
                    }
        }

        public function checkforacceptedQty($store_code , $sku , $submittedqty){

            $storeinventoryValue = 0;

               $storeinventorycollection = $this->storeinventoryFactory
                                            ->create()
                                            ->getCollection()
                                            ->addFieldToFilter('sku' , array('eq' => $sku))
                                            ->addFieldToFilter('store_id' , array('eq' => $store_code))
                                            ->getData();

                                            foreach($storeinventorycollection as $storeinventoryvalues){
                                                 $storeinventoryValue = $storeinventoryvalues['inventory'];
                                                 break;
                                            }



                                            if($storeinventoryValue >= $submittedqty){

                                              return true;

                                            }else{
                                              return false;
                                            }

        }

        public function getStoreinventoryQty($store_code , $sku){

          $storeinventoryValue = 0 ;

               $storeinventorycollection = $this->storeinventoryFactory->create()
                                            ->getCollection()
                                            ->addFieldToFilter('sku' , array('eq' => $sku))
                                            ->addFieldToFilter('store_id' , array('eq' => $store_code))
                                            ->getData();


                                            foreach($storeinventorycollection as $storeinventoryvalues){
                                                $storeinventoryValue = $storeinventoryvalues['inventory'];
                                            }

                                              return $storeinventoryValue;

        }




      public function sendPicklistToWarehouse($store_code , $ordersplit_item_id , $row_id , $order_id){

             $OrderObj =  $this->orderFactoryData->create()->load($order_id);
             $Orderincrementid = $OrderObj->getIncrementId();
            if($store_code == ""){

              return $mail_status = false;
            }


            $store_id = $this->storemanagerhelper->getStoreIdByStoreCode($store_code);
            $store_obj = $this->storemanagerFactory->create()->load($store_id);
            $store_type = $store_obj->getStoreType();
            $aEmailsids = array();

            if($store_type == 'store'){

                  $storemanagerWarehouseCollection = $this->storemanagerFactory
                                                        ->create()
                                                        ->getCollection()
                                                        ->addFieldToFilter('store_type' , array('eq' => 'warehouse'))
                                                        ->getData();

                                                        foreach($storemanagerWarehouseCollection as $storeData){
                                                            $aEmailsids [] = $storeData['store_emailid'];
                                                            $aWarehouseName [] = $storeData['store_name'];
                                                        }
            }elseif($store_type == 'warehouse'){
                $aEmailsids []= $store_obj->getStoreEmailid();
                $aWarehouseName [] = $store_obj->getStoreName();
            }

            $aEmailsids = array_unique($aEmailsids);

            $message = '';
            $path   = 'pub/media/picklist_warehouse/';
            $filename = 'picklist_'.$ordersplit_item_id.'.pdf';

            if(!file_exists($path.$filename)){

                $status = $this->createPdf($filename  , $path , 'picklist_creation' , $row_id , $row_id);

                if(!$status){
                  exit('File creating issue');
                }
            }

            $emailTempVariables['order_id'] = $Orderincrementid;
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');

            $domain_name =  $scopeConfig->getValue('sms_configuration/sms_setting/domain_name');
            $domain_email_id =  $scopeConfig->getValue('sms_configuration/sms_setting/domain_email_id');


            foreach($aEmailsids as $key => $emailaddress){

              $receiverInfo = ['name' => $aWarehouseName[$key], 'email' => $emailaddress];
              $senderInfo = ['name' => $domain_name , 'email' => $domain_email_id ];
              $emailTempVariables['order_id'] = $Orderincrementid;
              $emailTempVariables['name'] = $aWarehouseName[$key];

                $this->emailidshelper->emailTemplate('picklist' , $emailTempVariables ,$senderInfo,$receiverInfo,$path ,  $filename);
                // $this->emailidshelper->smsTemplate('picklist' , $emailTempVariables ,$senderInfo,$receiverInfo,$path ,  $filename);
            }



            /*$mail_status = $this->mail_attachment( $filename, $path , $aEmailsids , '2xl@domain.com' , '2Xl' , '' , 'Picklist for warehouse' , $message );*/

            //return $mail_status;
            return true;

        }

        public function mail_attachment($filename, $path, $aMailto, $from_mail, $from_name, $replyto, $subject, $message) {
          $file = $path.$filename;
           $file_size = filesize($file);
           $handle = fopen($file, "r");
           $content = fread($handle, $file_size);
           fclose($handle);
           $content = chunk_split(base64_encode($content));
           $uid = md5(uniqid(time()));
           $header = "From: ".$from_name." <".$from_mail.">\n";
            $header .= "Reply-To: ".$replyto."\n";
            $header .= "MIME-Version: 1.0\n";
            $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\n\n";
            $emessage= "--".$uid."\n";
            $emessage.= "Content-type:text/plain; charset=iso-8859-1\n";
            $emessage.= "Content-Transfer-Encoding: 7bit\n\n";
            $emessage .= $message."\n\n";
            $emessage.= "--".$uid."\n";
            $emessage .= "Content-Type: application/octet-stream; name=\"".$filename."\"\n"; // use different content types here
            $emessage .= "Content-Transfer-Encoding: base64\n";
            $emessage .= "Content-Disposition: attachment; filename=\"".$filename."\"\n\n";
            $emessage .= $content."\n\n";
            $emessage .= "--".$uid."--";
            $sMailTo = implode(',' , $aMailto);
           if (mail($sMailTo,$subject,$emessage,$header)) {
           return true; // or use booleans here
           } else {
           return false;
           }
        }

        public function getDefaultImageforProduct(){

          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
          $default_img =  $scopeConfig->getValue('catalog/placeholder/image_placeholder');
          $product_fullimageDefault = $this->baseurl->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product/placeholder/'.$default_img;
          return $product_fullimageDefault;

        }




        function createPdf($file_name , $path , $action_id , $id , $row_id){

          if($action_id == 'invoice_creation'){
              $html = $this->getInvoiceHtmlForPdf($id , $row_id);
              $pdftitle =  preg_replace('/\\.[^.\\s]{3,4}$/', '', $file_name);
          }elseif($action_id == 'master_invoice_creation'){
            $html = $this->getMasterInvoiceHtmlForPdf($id , $row_id);
            $pdftitle =  preg_replace('/\\.[^.\\s]{3,4}$/', '', $file_name);
          }elseif($action_id == 'picklist_creation'){
            $html = $this->getPicklistHtmlPdf($row_id);
             $pdftitle =  preg_replace('/\\.[^.\\s]{3,4}$/', '', $file_name);
          }elseif($action_id == 'shipment_creation'){
            $html = $this->getShipmentHtmlPdf($id , $row_id);
            $pdftitle =  preg_replace('/\\.[^.\\s]{3,4}$/', '', $file_name);
          }elseif($action_id == 'customer_invoice'){
            $html = $this->getCustomerInvoiceHtmlPdf($id);
             $pdftitle =  preg_replace('/\\.[^.\\s]{3,4}$/', '', $file_name);
          }
          $tcpdf = new TCPDF_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                  $tcpdf->SetCreator(PDF_CREATOR);
                  $tcpdf->SetTitle($pdftitle);
                  $tcpdf->SetSubject($pdftitle);
                  //$tcpdf->SetKeywords('TCPDF, PDF, example, test, guide');
                  $Header_title = $pdftitle ;
                  //$tcpdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . $Header_title, PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
                  $tcpdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
                  $tcpdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                  $tcpdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
                  $tcpdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                  $tcpdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                  $tcpdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                  $tcpdf->SetFooterMargin(PDF_MARGIN_FOOTER);
                  $tcpdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                  $tcpdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                  if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
                      require_once(dirname(__FILE__) . '/lang/eng.php');
                      $tcpdf->setLanguageArray($l);
                  }
                  $tcpdf->setFontSubsetting(true);
                  $lg = Array();
                  $lg['a_meta_charset'] = 'UTF-8';
                  $tcpdf->setLanguageArray($lg);
                  $tcpdf->SetFont('freesans', '', 12);
                  $tcpdf->setPrintFooter(false);
                  $tcpdf->AddPage();
                  $tcpdf->writeHTML($html, true, false, true, false, '');
                  $tcpdf->lastPage();
                  $fullname = __DIR__.'/'.$file_name;
                  $tcpdf->Output($fullname, 'F');
                   $file_transfer_status = rename($fullname, $path.$file_name);
                   return $file_transfer_status;

        }


        function getShipmentHtmlPdf($shipment_id , $row_id){


          $shipmentObj = $this->shipmentFactory->create()->load($shipment_id , 'increment_id');
          $iShipment_id = $shipmentObj->getId();
          $shipmentincrementid = $shipment_id;
          $order_id = $shipmentObj->getOrderId();
          $order_incremenid = $this->orderFactoryData->create()->load($order_id)->getIncrementId();

          $sShipment_date = $shipmentObj->getCreatedAt();
          $aShipment_date = explode(' ' , $sShipment_date);
          $aShipmentdate = explode('-' , $aShipment_date[0]);

          $UShipment_date = mktime(0, 0 , 0 , $aShipmentdate[1] , $aShipmentdate[2] , $aShipmentdate[0] );
          $sFormatShipmentDate = date('d-M-Y' , $UShipment_date);

          $addressObj = $this->addressFactory->create()->load($order_id , 'parent_id');
          $Customer_name = $addressObj->getFirstname().' '.$addressObj->getLastname();
          $RegionName = $addressObj->getRegion();
          $PostCode = $addressObj->getPostcode();
           $streetName = $addressObj->getStreet()[0];
          $city = $addressObj->getCity();
          $emailid = $addressObj->getEmail();
          $telephone = $addressObj->getTelephone();
          $tracknumber = $this->shippmentTrackfactory->create()->load($iShipment_id , 'parent_id')->getTrackNumber();

          $html_items = "";


          $ordersplitobj = $this->ordersplitFactory->create()->load($row_id);
          $ordersplitId = $ordersplitobj->getOrderItemId();

          $ShipmentItemsCollection = $this->shipmentItemFactory->create()->getCollection()
                                      ->addFieldToFilter('parent_id' , array('eq' => $iShipment_id))
                                      ->getData();

                                      foreach($ShipmentItemsCollection as $shipmentData){
                                        $productObj = $this->productfactory->create()->load($shipmentData['product_id']);
                                        $productsku = $shipmentData['sku'];
                                        $productimage  = $productObj->getData('image');

                                        $product_fullimage = $this->baseurl->getStore()->getBaseUrl().'pub/media/catalog/product/'.$productimage;
                                        $handle = get_headers($product_fullimage ,1 );

                                        if(strpos($handle [0] , '200' ) !== false){

                                        }else{
                                          $product_fullimage = $this->getDefaultImageforProduct();
                                        }

                                        // echo $product_image = $this->storescopeInterface
                                        //                       ->getStore()
                                        //                       ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product'.$productObj->getImage();
                                        // exit;
                                        $productName = $shipmentData['name'];
                                        $qty = round($shipmentData['qty']);


                                        $html_items .= '<tr>

                                            <td style="border-bottom:1px solid #000000;">
                                            <img src="'.$product_fullimage.'" border="0" height="155" width="103" /></td>
                                            <td style="border-bottom:1px solid #000000;">Sub Order: '.$ordersplitId.'<br/>Product Name: '.$productName.'<br/>Item Code: '.$productsku.'</td>
                                            <td style="border-bottom:1px solid #000000;">'.$qty.'</td>
                                        </tr>';
                                      }


            $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>Invoice</title>
            </head>
            <body>
            <table style="font-size:small; line-height: 20px" cellpadding="0" cellspacing="0">
                <tr>
                    <td colspan="3"><img src="'.$this->getLogoSrc().'" border="0" height="80" width="100" /></td>
                </tr>
                <tr>
                    <td style="font-size:medium;">
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="line-height: 30px;"><strong>Customer Details</strong></td>
                            </tr>
                            <tr>
                                <td>'.$Customer_name.'</td>
                            </tr>
                            <tr>
                                <td>'.$streetName.'</td>
                            </tr>
                            <tr>
                                <td>'.$RegionName.'</td>
                            </tr>
                            <tr>
                                <td>'.$city.'</td>
                            </tr>
                            <tr>
                                <td>Mobile Number: '.$telephone.'</td>
                            </tr>
                            <tr>
                                <td>Email: '.$emailid.'</td>
                            </tr>
                        </table>
                    </td>
                    <td style="text-align:center;"><strong style="font-size: xx-large;">Shipment</strong></td>
                    <td style="font-size:medium;">
                        <table>
                            <tr>
                                <td><strong>Shipment Date: </strong>'.$sFormatShipmentDate.'</td>
                            </tr>
                            <tr>
                                <td><strong>Shipment Number: </strong>'.$shipmentincrementid.'</td>
                            </tr>
                            <tr>
                                <td><strong>Tracking Number: </strong>'.$tracknumber.'</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3" style="font-size:medium;text-align:center"><strong>ORDER ID: '.$order_incremenid.'</strong></td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <table border="0" cellpadding="10">
                            <tr>
                                <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="12%">&nbsp;</td>
                                <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="68%">Item Details</td>
                                <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="20%">Qty</td>
                            </tr>
                            '.$html_items.'
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="line-height:40px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align:center;line-height:20px;">Toll Free: 800 2XL (295) | www.2xlme.com | E-mail: customercare@2xlme.com <br/>DUBAI • ABU DHABI • AL AIN • SHARJAH • FUJAIRAH</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align:center; line-height:30px;">
                           <a href="https://www.facebook.com/2XLfurniture/"><img src="'.$this->baseurl->getStore()->getBaseUrl().'pub/media/pdf_images/facebook.png"  border="0" height="30" width="30"></a>
                            <a href="https://www.instagram.com/2xlfurniture/?hl=en"><img src="'.$this->baseurl->getStore()->getBaseUrl().'pub/media/pdf_images/instagram.png"  border="0" height="30" width="30"></a>
                        </td>
                    </tr>
                </table>
                </body>
                </html>
            ';
            return $html;

        }

        public function getCustomerInvoiceHtmlPdf($order_id){

                $orderobj = $this->orderFactoryData->create()->load($order_id);
                //$invoice_details = $orderobj->getInvoiceCollection();
                $order_items_data = $orderobj->getAllItems();
                $order_incrementid = $orderobj->getIncrementId();
                $addressObj = $this->addressFactory->create()->load($order_id , 'parent_id');
                $Customer_name = $addressObj->getFirstname().' '.$addressObj->getLastname();
                $RegionName = $addressObj->getRegion();
                $PostCode = $addressObj->getPostcode();
                 $streetName = $addressObj->getStreet()[0];
                $city = $addressObj->getCity();
                $emailid = $addressObj->getEmail();
                $telephone = $addressObj->getTelephone();
                $ClubInvoiceSubtotal = 0;
                $ClubInvoiceDiscount = 0;
                $ClubInvoiceShippingCharges = 0;
                $ClubInvoiceTaxAmount = 0;
                $ClubInvoiceBaseGrandTotal = 0;
                $html_items = "";


                  $OrdersplitId = "";

                foreach ($order_items_data as $_items) {


                                                      $productObj = $this->productfactory->create()
                                                                ->load($_items->getProductId());
                                                      $productsku = $_items->getSku();
                                                      $productimage  = $productObj->getData('image');
                                                      $product_fullimage = $this->baseurl->getStore()->getBaseUrl().'pub/media/catalog/product/'.$productimage;
                                                      $handle = get_headers($product_fullimage ,1 );



                                                      if(strpos($handle [0] , '200' ) !== false){

                                                      }else{
                                                        $product_fullimage = $this->getDefaultImageforProduct();
                                                      }

                                                      $productName = $_items->getName();
                                                      $qty = round($_items->getQtyOrdered());
                                                      $Unitprice = round($_items->getBasePrice());
                                                      $Totalamount = round($_items->getRowTotal());
                                                      $DiscountAmount = round($_items->getBaseDiscountAmount());
                                                      $TotalAmountExcldiscount = ($Totalamount)-($DiscountAmount);

                                                      $html_items .= '<tr>
                                                          <td style="border-bottom:1px solid #000000;"><img src="'.$product_fullimage.'" border="0" height="155" width="103" /></td>
                                                          <td style="border-bottom:1px solid #000000;">Sub Order: '.$OrdersplitId.'<br/>Product Name: '.$productName.'<br/>Item Code: '.$productsku.'</td>
                                                          <td style="border-bottom:1px solid #000000;">'.$qty.'</td>
                                                          <td style="border-bottom:1px solid #000000;">'.$Unitprice.'</td>
                                                          <td style="border-bottom:1px solid #000000;">'.$DiscountAmount.'</td>
                                                          <td style="border-bottom:1px solid #000000;">'.$TotalAmountExcldiscount.'</td>
                                                      </tr>';




                }

                $subtotalOrder = round($orderobj->getBaseSubtotal());
                $discountOrder = round($orderobj->getDiscountAmount());
                $shipping_charges_Order = round($orderobj->getShippingAmount());
                $taxAmountOrder = round($orderobj->getBaseTaxAmount());
                $basegrandTotalOrder = round($orderobj->getBaseGrandTotal());
                /*************/


                  $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                  <html xmlns="http://www.w3.org/1999/xhtml">
                  <head>
                  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                  <title>Master Invoice</title>
                  </head>
                  <body>
                  <table style="font-size:small; line-height: 20px" cellpadding="0" cellspacing="0">
                      <tr>
                          <td colspan="3"><img src="'.$this->getLogoSrc().'" border="0" height="80" width="100" /></td>
                      </tr>
                      <tr>
                          <td style="font-size:medium;">
                              <table cellpadding="0" cellspacing="0">
                                  <tr>
                                      <td style="line-height: 30px;"><strong>Customer Details</strong></td>
                                  </tr>
                                  <tr>
                                      <td>'.$Customer_name.'</td>
                                  </tr>
                                  <tr>
                                      <td>'.$streetName.'</td>
                                  </tr>
                                  <tr>
                                      <td>'.$RegionName.'</td>
                                  </tr>
                                  <tr>
                                      <td>'.$city.'</td>
                                  </tr>
                                  <tr>
                                      <td>Mobile Number: '.$telephone.'</td>
                                  </tr>
                                  <tr>
                                      <td>Email: '.$emailid.'</td>
                                  </tr>
                              </table>
                          </td>
                          <td style="text-align:center;"><strong style="font-size: xx-large;">Invoice</strong></td>

                      </tr>
                      <tr>
                          <td colspan="3">&nbsp;</td>
                      </tr>
                      <tr>
                          <td colspan="3" style="font-size:medium;text-align:center;"><strong>ORDER ID: '.$order_incrementid.'</strong></td>
                      </tr>
                      <tr>
                          <td colspan="3">&nbsp;</td>
                      </tr>
                      <tr>
                          <td colspan="3">
                              <table border="0" cellpadding="10">
                                  <tr>
                                      <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="12%">&nbsp;</td>
                                      <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="38%">Item Details</td>
                                      <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="8%">Qty</td>
                                      <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="15%">Unit Price</td>
                                      <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="12%">Discount</td>
                                      <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="15%">Amount</td>
                                  </tr>
                                  '.$html_items.'
                                  <tr>
                                      <td colspan="2"></td>
                                      <td colspan="4">
                                          <table>
                                              <tr>
                                                  <td style="line-height:20px;" colspan="2"><strong>Product Subtotal:</strong></td>
                                                  <td style="line-height:20px;">AED '.$subtotalOrder.'</td>
                                              </tr>
                                              <tr>
                                                  <td style="line-height:20px;" colspan="2"><strong>Shipping Charges:</strong></td>
                                                  <td style="line-height:20px;">AED '.$shipping_charges_Order.'</td>
                                              </tr>
                                              <tr>
                                                  <td style="line-height:20px;" colspan="2"><strong>VAT:</strong></td>
                                                  <td style="line-height:20px;">AED '.$taxAmountOrder.'</td>
                                              </tr>
                                              <tr>
                                                  <td style="line-height:30px;" colspan="2"><strong>Discount:</strong></td>
                                                  <td style="line-height:30px;">AED '.$discountOrder.'</td>
                                              </tr>
                                              <tr>
                                                  <td style="line-height:40px;border-top:1px solid #000000;font-size:large;" colspan="2"><strong>Grand Total:</strong></td>
                                                  <td style="line-height:40px;border-top:1px solid #000000;font-size:large;"><strong>AED '.$basegrandTotalOrder.'</strong></td>
                                              </tr>
                                          </table>
                                      </td>

                                  </tr>
                              </table>
                          </td>
                      </tr>
                      <tr>
                          <td colspan="6" style="line-height:40px;">&nbsp;</td>
                      </tr>
                      <tr>
                          <td colspan="6" style="text-align:center;line-height:20px;">Toll Free: 800 2XL (295) | www.2xlme.com | E-mail: customercare@2xlme.com <br/>DUBAI • ABU DHABI • AL AIN • SHARJAH • FUJAIRAH</td>
                      </tr>
                      <tr>
                          <td colspan="6" style="text-align:center; line-height:30px;">
                          <a href="https://www.facebook.com/2XLfurniture/"><img src="'.$this->baseurl->getStore()->getBaseUrl().'pub/media/pdf_images/facebook.png"  border="0" height="30" width="30"></a>
                           <a href="https://www.instagram.com/2xlfurniture/?hl=en"><img src="'.$this->baseurl->getStore()->getBaseUrl().'pub/media/pdf_images/instagram.png"  border="0" height="30" width="30"></a>
                          </td>
                      </tr>
                  </table>
                  </body>
                  </html>
                  ';

                  return $html;



        }



        function getInvoiceHtmlForPdf($invoice_id , $row_id){


          $invoiceObj = $this->invoiceFactory->create()->load($invoice_id , 'increment_id');
          $iInvoice_id = $invoiceObj->getId();
          $invoiceincrementid = $invoice_id;
          $order_id = $invoiceObj->getOrderId();

          $order_incremenid = $this->orderFactoryData->create()->load($order_id)->getIncrementId();

          $sInvoice_date = $invoiceObj->getCreatedAt();
          $aInvoice_date = explode(' ' , $sInvoice_date);
          $aInvoicedate = explode('-' , $aInvoice_date[0]);

          $UInvoice_date = mktime(0, 0 , 0 , $aInvoicedate[1] , $aInvoicedate[2] , $aInvoicedate[0] );
          $sFormatInvoiceDate = date('d-M-Y' , $UInvoice_date);

          $addressObj = $this->addressFactory->create()->load($order_id , 'parent_id');
          $Customer_name = $addressObj->getFirstname().' '.$addressObj->getLastname();
          $RegionName = $addressObj->getRegion();
          $PostCode = $addressObj->getPostcode();
           $streetName = $addressObj->getStreet()[0];
          $city = $addressObj->getCity();
          $emailid = $addressObj->getEmail();
          $telephone = $addressObj->getTelephone();
          $subtotalInvoice = round($invoiceObj->getSubtotal());
          $discountInvoice = round($invoiceObj->getBaseDiscountAmount());
          $shipping_charges = round($invoiceObj->getBaseShippingAmount());
          $taxAmountInvoice = round($invoiceObj->getTaxAmount());
          $basegrandTotal = round($invoiceObj->getBaseGrandTotal());
          $html_items = "";


          $ordersplitobj = $this->ordersplitFactory->create()->load($row_id);
          $ordersplitId = $ordersplitobj->getOrderItemId();

          $InvoiceItemsCollection = $this->InvoiceItemfactory->create()->getCollection()
                                      ->addFieldToFilter('parent_id' , array('eq' => $iInvoice_id))
                                      ->getData();


                                      foreach($InvoiceItemsCollection as $invoiceData){
                                        $productObj = $this->productfactory->create()->load($invoiceData['product_id']);
                                        $productsku = $invoiceData['sku'];
                                        $productimage  = $productObj->getData('image');

                                      $product_fullimage = $this->baseurl->getStore()->getBaseUrl().'pub/media/catalog/product/'.$productimage;
                                      $handle = get_headers($product_fullimage ,1 );

                                      if(strpos($handle [0] , '200' ) !== false){

                                      }else{
                                        $product_fullimage = $this->getDefaultImageforProduct();
                                      }



                                        // echo $product_image = $this->storescopeInterface
                                        //                       ->getStore()
                                        //                       ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product'.$productObj->getImage();
                                        // exit;
                                        $productName = $invoiceData['name'];
                                        $qty = round($invoiceData['qty']);
                                        $Unitprice = round($invoiceData['base_price_incl_tax']);
                                        $Totalamount = ($qty * $Unitprice);
                                        $DiscountAmount = round($invoiceData['base_discount_amount']);
                                        $TotalAmountExcldiscount = ($Totalamount)-($DiscountAmount);

                                        $html_items .= '<tr>
                                            <td style="border-bottom:1px solid #000000;">
                                            <img src="'.$product_fullimage.'" border="0" height="155" width="103" /></td>
                                            <td style="border-bottom:1px solid #000000;">Sub Order: '.$ordersplitId.'<br/>Product Name: '.$productName.'<br/>Item Code: '.$productsku.'</td>
                                            <td style="border-bottom:1px solid #000000;">'.$qty.'</td>
                                            <td style="border-bottom:1px solid #000000;">'.$Unitprice.'</td>
                                            <td style="border-bottom:1px solid #000000;">'.$DiscountAmount.'</td>
                                            <td style="border-bottom:1px solid #000000;">'.$TotalAmountExcldiscount.'</td>
                                        </tr>';
                                      }


            $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>Invoice</title>
            </head>
            <body>
            <table style="font-size:small; line-height: 20px" cellpadding="0" cellspacing="0">
                <tr>
                    <td colspan="2"><img src="'.$this->getLogoSrc().'" border="0" height="80" width="100" /></td>
                    <td style="text-align:left;">
                      <strong style="font-size: xx-large;">INVOICE</strong>
                    </td>
                </tr>
                <tr>
                    <td style="font-size:medium;" colspan="2">
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="line-height: 30px;"><strong>Customer Details</strong></td>
                            </tr>
                            <tr>
                                <td>'.$Customer_name.'</td>
                            </tr>
                            <tr>
                                <td>'.$streetName.'</td>
                            </tr>
                            <tr>
                                <td>'.$RegionName.'</td>
                            </tr>
                            <tr>
                                <td>'.$city.'</td>
                            </tr>
                            <tr>
                                <td>Mobile Number: '.$telephone.'</td>
                            </tr>
                            <tr>
                                <td>Email: '.$emailid.'</td>
                            </tr>
                        </table>
                    </td>
                    <td style="font-size:medium;">
                        <table>
                            <tr>
                                <td><strong>Invoice Date: </strong>'.$sFormatInvoiceDate.'</td>
                            </tr>
                            <tr>
                                <td><strong>Invoice Number: </strong>'.$invoiceincrementid.'</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3" style="font-size:medium;"><strong>ORDER ID: '.$order_incremenid.'</strong></td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <table border="0" cellpadding="10">
                            <tr>
                                <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="12%">&nbsp;</td>
                                <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="38%">Item Details</td>
                                <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="8%">Qty</td>
                                <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="15%">Unit Price</td>
                                <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="12%">Discount</td>
                                <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="15%">Amount</td>
                            </tr>
                            '.$html_items.'
                            <tr>
                                <td colspan="2"></td>
                                <td colspan="4">
                                    <table>
                                        <tr>
                                            <td style="line-height:20px;" colspan="2"><strong>Product Subtotal:</strong></td>
                                            <td style="line-height:20px;">'.$this->getStoreCurrenycode().'  '.$subtotalInvoice.'</td>
                                        </tr>
                                        <tr>
                                            <td style="line-height:20px;" colspan="2"><strong>Shipping Charges:</strong></td>
                                            <td style="line-height:20px;">'.$this->getStoreCurrenycode().'  '.$shipping_charges.'</td>
                                        </tr>
                                        <tr>
                                            <td style="line-height:20px;" colspan="2"><strong>TAX:</strong></td>
                                            <td style="line-height:20px;">'.$this->getStoreCurrenycode().'  '.$taxAmountInvoice.'</td>
                                        </tr>
                                        <tr>
                                            <td style="line-height:30px;" colspan="2"><strong>Discount:</strong></td>
                                            <td style="line-height:30px;">'.$this->getStoreCurrenycode().'  '.$discountInvoice.'</td>
                                        </tr>
                                        <tr>
                                            <td style="line-height:40px;border-top:1px solid #000000;font-size:large;" colspan="2"><strong>Grand Total:</strong></td>
                                            <td style="line-height:40px;border-top:1px solid #000000;font-size:large;"><strong>'.$this->getStoreCurrenycode().'  '.$basegrandTotal.'</strong></td>
                                        </tr>
                                    </table>
                                </td>

                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="6" style="line-height:40px;">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="6" style="text-align:center;line-height:20px;">Toll Free: 800 2XL (295) | www.2xlme.com | E-mail: customercare@2xlme.com <br/>DUBAI • ABU DHABI • AL AIN • SHARJAH • FUJAIRAH</td>
                </tr>
                <tr>
                    <td colspan="6" style="text-align:center; line-height:30px;">
                    <a href="https://www.facebook.com/2XLfurniture/"><img src="'.$this->baseurl->getStore()->getBaseUrl().'pub/media/pdf_images/facebook.png"  border="0" height="30" width="30"></a>
                     <a href="https://www.instagram.com/2xlfurniture/?hl=en"><img src="'.$this->baseurl->getStore()->getBaseUrl().'pub/media/pdf_images/instagram.png"  border="0" height="30" width="30"></a>
                    </td>
                </tr>
            </table>
            </body>
            </html>
            ';


            return $html;

        }



        function getMasterInvoiceHtmlForPdf($order_id , $row_id){

          $orderobj = $this->orderFactoryData->create()->load($order_id);
          $invoice_details = $orderobj->getInvoiceCollection();
          $order_incrementid = $orderobj->getIncrementId();
          $addressObj = $this->addressFactory->create()->load($order_id , 'parent_id');
          $Customer_name = $addressObj->getFirstname().' '.$addressObj->getLastname();
          $RegionName = $addressObj->getRegion();
          $PostCode = $addressObj->getPostcode();
           $streetName = $addressObj->getStreet()[0];
          $city = $addressObj->getCity();
          $emailid = $addressObj->getEmail();
          $telephone = $addressObj->getTelephone();
          $ClubInvoiceSubtotal = 0;
          $ClubInvoiceDiscount = 0;
          $ClubInvoiceShippingCharges = 0;
          $ClubInvoiceTaxAmount = 0;
          $ClubInvoiceBaseGrandTotal = 0;
          $html_items = "";


            $OrdersplitId = "";

          foreach ($invoice_details as $_invoice) {

            $aInvoiceIncrementIds [] = $_invoice->getIncrementId();

                  $InvoiceItemsCollection = $this->InvoiceItemfactory->create()->getCollection()
                                              ->addFieldToFilter('parent_id' , array('eq' => $_invoice->getId()))
                                              ;


                                              $OrdersplitId = $this->ordersplitFactory->create()->load($_invoice->getIncrementId() , 'invoice_id')->getOrderItemId();

                                              foreach($InvoiceItemsCollection as $invoiceData){


                                                $productObj = $this->productfactory->create()
                                                          ->load($invoiceData->getProductId());
                                                $productsku = $invoiceData->getSku();
                                                $productimage  = $productObj->getData('image');

                                                $product_fullimage = $this->baseurl->getStore()->getBaseUrl().'pub/media/catalog/product/'.$productimage;
                                                $handle = get_headers($product_fullimage ,1 );

                                                if(strpos($handle [0] , '200' ) !== false){

                                                }else{
                                                  $product_fullimage = $this->getDefaultImageforProduct();
                                                }
                                                // echo $product_image = $this->storescopeInterface
                                                //                       ->getStore()
                                                //                       ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product'.$productObj->getImage();
                                                // exit;
                                                $productName = $invoiceData->getName();
                                                $qty = round($invoiceData->getQty());
                                                $Unitprice = round($invoiceData->getPrice());
                                                $Totalamount = ($qty * $Unitprice);
                                                $DiscountAmount = round($invoiceData->getBaseDiscountAmount());
                                                $TotalAmountExcldiscount = ($Totalamount)-($DiscountAmount);

                                                $html_items .= '<tr>
                                                    <td style="border-bottom:1px solid #000000;"><img src="'.$product_fullimage.'" border="0" height="155" width="103" /></td>
                                                    <td style="border-bottom:1px solid #000000;">Sub Order: '.$OrdersplitId.'<br/>Product Name: '.$productName.'<br/>Item Code: '.$productsku.'</td>
                                                    <td style="border-bottom:1px solid #000000;">'.$qty.'</td>
                                                    <td style="border-bottom:1px solid #000000;">'.$Unitprice.'</td>
                                                    <td style="border-bottom:1px solid #000000;">'.$DiscountAmount.'</td>
                                                    <td style="border-bottom:1px solid #000000;">'.$TotalAmountExcldiscount.'</td>
                                                </tr>';
                                              }

                                              $subtotalInvoice = round($_invoice->getSubtotal());
                                              $discountInvoice = round($_invoice->getBaseDiscountAmount());
                                              $shipping_charges = round($_invoice->getBaseShippingAmount());
                                              $taxAmountInvoice = round($_invoice->getTaxAmount());
                                              $basegrandTotal = round($_invoice->getBaseGrandTotal());


                                              $ClubInvoiceSubtotal += $subtotalInvoice;
                                              $ClubInvoiceDiscount += $discountInvoice;
                                              $ClubInvoiceShippingCharges += $shipping_charges;
                                              $ClubInvoiceTaxAmount += $taxAmountInvoice;
                                              $ClubInvoiceBaseGrandTotal += $basegrandTotal;

          }
          /*************/




          $sInvoiceIncrementIds = implode(',' , $aInvoiceIncrementIds);



            $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>Master Invoice</title>
            </head>
            <body>
            <table style="font-size:small; line-height: 20px" cellpadding="0" cellspacing="0">
                <tr>
                    <td colspan="2"><img src="'.$this->getLogoSrc().'" border="0" height="80" width="100" /></td>
                    <td style="text-align:left;">
                      <strong style="font-size: xx-large;"> Master Invoice</strong>
                    </td>
                </tr>
                <tr>
                    <td style="font-size:medium;" colspan = "2">
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="line-height: 30px;"><strong>Customer Details</strong></td>
                            </tr>
                            <tr>
                                <td>'.$Customer_name.'</td>
                            </tr>
                            <tr>
                                <td>'.$streetName.'</td>
                            </tr>
                            <tr>
                                <td>'.$RegionName.'</td>
                            </tr>
                            <tr>
                                <td>'.$city.'</td>
                            </tr>
                            <tr>
                                <td>Mobile Number: '.$telephone.'</td>
                            </tr>
                            <tr>
                                <td>Email: '.$emailid.'</td>
                            </tr>
                        </table>
                    </td>
                    <td style="font-size:medium;">
                        <table>
                            <tr>
                                <td><strong>Invoice Number\'s: </strong><br />'.$sInvoiceIncrementIds.'</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3" style="font-size:medium;"><strong>ORDER ID: '.$order_incrementid.'</strong></td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <table border="0" cellpadding="10">
                            <tr>
                                <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="12%">&nbsp;</td>
                                <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="38%">Item Details</td>
                                <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="8%">Qty</td>
                                <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="15%">Unit Price</td>
                                <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="12%">Discount</td>
                                <td style="border-top:1px solid #000000;border-bottom:1px solid #000000;line-height:12px;" width="15%">Amount</td>
                            </tr>
                            '.$html_items.'
                            <tr>
                                <td colspan="2"></td>
                                <td colspan="4">
                                    <table>
                                        <tr>
                                            <td style="line-height:20px;" colspan="2"><strong>Product Subtotal:</strong></td>
                                            <td style="line-height:20px;">'.$this->getStoreCurrenycode().'  '.$ClubInvoiceSubtotal.'</td>
                                        </tr>
                                        <tr>
                                            <td style="line-height:20px;" colspan="2"><strong>Shipping Charges:</strong></td>
                                            <td style="line-height:20px;">'.$this->getStoreCurrenycode().'  '.$ClubInvoiceShippingCharges.'</td>
                                        </tr>
                                        <tr>
                                            <td style="line-height:20px;" colspan="2"><strong>VAT:</strong></td>
                                            <td style="line-height:20px;">'.$this->getStoreCurrenycode().'  '.$ClubInvoiceTaxAmount.'</td>
                                        </tr>
                                        <tr>
                                            <td style="line-height:30px;" colspan="2"><strong>Discount:</strong></td>
                                            <td style="line-height:30px;">'.$this->getStoreCurrenycode().'  '.$ClubInvoiceDiscount.'</td>
                                        </tr>
                                        <tr>
                                            <td style="line-height:40px;border-top:1px solid #000000;font-size:large;" colspan="2"><strong>Grand Total:</strong></td>
                                            <td style="line-height:40px;border-top:1px solid #000000;font-size:large;"><strong>'.$this->getStoreCurrenycode().'  '.$ClubInvoiceBaseGrandTotal.'</strong></td>
                                        </tr>
                                    </table>
                                </td>

                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="6" style="line-height:40px;">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="6" style="text-align:center;line-height:20px;">Toll Free: 800 2XL (295) | www.2xlme.com | E-mail: customercare@2xlme.com <br/>DUBAI • ABU DHABI • AL AIN • SHARJAH • FUJAIRAH</td>
                </tr>
                <tr>
                    <td colspan="6" style="text-align:center; line-height:30px;">
                    <a href="https://www.facebook.com/2XLfurniture/"><img src="'.$this->baseurl->getStore()->getBaseUrl().'pub/media/pdf_images/facebook.png"  border="0" height="30" width="30"></a>
                     <a href="https://www.instagram.com/2xlfurniture/?hl=en"><img src="'.$this->baseurl->getStore()->getBaseUrl().'pub/media/pdf_images/instagram.png"  border="0" height="30" width="30"></a>
                    </td>
                </tr>
            </table>
            </body>
            </html>
            ';


            return $html;

        }



        function getPicklistHtmlPdf($row_id){


          $ordersplitobjData = $this->ordersplitFactory->create()->load($row_id);
          $order_item_id = $ordersplitobjData->getOrderItemId();
          $allocated_id = $ordersplitobjData->getAllocatedStoreids();
          $store_code = $this->storemanagerhelper->getStoreCodeByStoreId($allocated_id);
          $sOrder_items_data = $ordersplitobjData->getOrderItemsData();
          $aOrder_items_data = json_decode($sOrder_items_data , true);

          $aProductsData = array();


            foreach($aOrder_items_data as $key => $vOrder_items_data){

                $productObj = $this->productfactory->create()->load($vOrder_items_data['sku'] , 'sku');
                $ProductName = $productObj->getName();
                $aProductsData  [$key] ['sku'] =  $vOrder_items_data['sku'];
                $aProductsData  [$key] ['name'] =  $ProductName;
                $aProductsData  [$key] ['inventory'] = $vOrder_items_data['inventory'];
                $aProductsData  [$key] ['store_code'] = $store_code;


            }

          $order_id = $ordersplitobjData->getOrderId();
          $OrderObj = $this->orderFactoryData->create()->load($order_id);
          $order_increment_id = $OrderObj->getIncrementId();
          $sOrder_datetimestamp = $OrderObj->getCreatedAt();
          $iOrderGrandTotal = round($OrderObj->getBaseGrandTotal());
          $aOrderDate = explode(' ' , $sOrder_datetimestamp);
          $addressObj = $this->addressFactory->create()->load($order_id , 'parent_id');
          $Customer_name = $addressObj->getFirstname().' '.$addressObj->getLastname();
          $RegionName = $addressObj->getRegion();
          $PostCode = $addressObj->getPostcode();
           $streetName = $addressObj->getStreet()[0];
          $city = $addressObj->getCity();
          $emailid = $addressObj->getEmail();
          $telephone = $addressObj->getTelephone();
          $html_data = "";
          $productsQty = 0;


          foreach($aProductsData as $productdata){

            $productsQty += $productdata['inventory'];


            $html_data .= '<tr>
              <td style="">'.$productdata['sku'].'</td>
              <td style="">'.$productdata['name'].'</td>
              <td style="">DIPWH</td>
              <td style="">No</td>
              <td style="">'.$productdata['store_code'].'</td>
              <td style="">U921-3S</td>
              <td style="text-align:right">'.$productdata['inventory'].'</td>
              <td style="text-align:right">0</td>
            </tr>
            <tr>
              <td style="border-top:1px solid #000;border-bottom:1px solid #000;border-left:1px solid #000;border-right:none;">&nbsp;Bin No.</td>
              <td style="border-top:1px solid #000;border-bottom:1px solid #000;border-right:none;border-left:none">'.$order_item_id.'</td>
              <td style="border-top:1px solid #000;border-bottom:1px solid #000;border-right:none;border-left:none">&nbsp;</td>
              <td style="border-top:1px solid #000;border-bottom:1px solid #000;border-right:none;border-left:none">&nbsp;</td>
              <td style="border-top:1px solid #000;border-bottom:1px solid #000;border-right:none;border-left:none">&nbsp;</td>
              <td style="border-top:1px solid #000;border-bottom:1px solid #000;border-right:none;border-left:none">&nbsp;</td>
              <td style="border-top:1px solid #000;border-bottom:1px solid #000;border-right:none;border-left:none">&nbsp;</td>
              <td style="border-top:1px solid #000;border-bottom:1px solid #000;border-right:1px solid #000;border-left:none">&nbsp;</td>
            </tr>';

          }




            $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>Untitled Document</title>
            </head>

            <body>
            <table style="font-size:small; line-height: 20px" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td colspan="3" width="62%">
                        <table>
                            <tr>
                                <td style="font-size:x-large;">2XL FURNITURE &amp; HOME DECOR</td>
                            </tr>
                            <tr>
                                <td style="font-size:medium;">Behind Sharjah Mega Mall, SHARJAH</td>
                            </tr>
                            <tr>
                                <td style="font-size:medium;">Tel: 06-5754900</td>
                            </tr>
                        </table>
                    </td>
                    <td align="right" width="38%" rowspan="2">
                        <img src="'.$this->getLogoSrc().'" width="100" />
                    </td>
                </tr>
                <tr>
                    <td align="left" width="38%" colspan="2">&nbsp;</td>
                    <td align="center" width="24%">
                        <table>
                            <tr>
                                <td style="font-size:x-large;"><strong>Pick list</strong></td>
                            </tr>
                            <tr>
                                <td style="font-size:x-large;"><strong>PRINTED COPY</strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <table width="100%">
                          <tr>
                              <td colspan="2" style="font-size:medium;"><strong>Customer Code </strong></td>
                          </tr>
                          <tr>
                              <td colspan="2" style="font-size:medium;"><strong>'.$Customer_name.'</strong></td>
                          </tr>
                          <tr>
                              <td colspan="2">'.$streetName.' '.$PostCode.'</td>
                          </tr>
                          <tr>
                              <td colspan="2">'.$RegionName.'</td>
                          </tr>
                          <tr>
                              <td colspan="2">'.$city.'</td>
                          </tr>
                          <tr>
                              <td width="30%">Phone</td>
                              <td width="70%">'.$telephone.'</td>
                          </tr>
                          <tr>
                              <td width="30%">Contact</td>
                              <td>'.$Customer_name.'</td>
                          </tr>
                          <tr>
                              <td width="30%">Sales Person</td>
                              <td>1322 Abdul Gani Afrooz</td>
                          </tr>
                        </table>
                    </td>
                    <td></td>
                    <td colspan="2">
                        <table width="100%">
                        <tr>
                            <td colspan="2" align="right">Page 1</td>
                        </tr>
                        <tr>
                            <td align="right" width="70%"><strong>Delivery Order No. &nbsp;&nbsp;</strong></td>
                            <td width="30%"><strong>D42834</strong></td>
                        </tr>
                        <tr>
                            <td align="right" width="70%">Delivery Date &nbsp;&nbsp;</td>
                            <td width="30%">&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="right" width="70%">Trans No. &nbsp;&nbsp;</td>
                            <td width="30%">31012226</td>
                        </tr>
                        <tr>
                            <td align="right" width="70%">Sales Order No. &nbsp;&nbsp;</td>
                            <td width="30%">'.$order_increment_id.'</td>
                        </tr>
                        <tr>
                            <td align="right" width="70%">Sales Order Date &nbsp;&nbsp;</td>
                            <td width="30%">'.$aOrderDate[0].'</td>
                        </tr>
                        <tr>
                            <td align="right" width="70%">Total Amount &nbsp;&nbsp;</td>
                            <td width="30%">'.$iOrderGrandTotal.'</td>
                        </tr>
                        <tr>
                            <td align="right" width="70%">Amount Received &nbsp;&nbsp;</td>
                            <td width="30%">0</td>
                        </tr>
                        <tr>
                            <td align="right" width="70%">Balance Amount &nbsp;&nbsp;</td>
                            <td width="30%">0</td>
                        </tr>
                        <tr>
                            <td align="right" width="70%">Status &nbsp;&nbsp;</td>
                            <td width="30%">Paid</td>
                        </tr>
                        <tr>
                            <td align="right" width="70%"><strong>Reclasif No.: &nbsp;&nbsp;</strong></td>
                            <td width="30%">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                    </table>
                    </td>
                </tr>
              <tr>
                <td colspan="4"><table width="100%" border="0" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="border-top:1px solid #000;border-bottom:1px solid #000; font-weight:bold" width="10%">Item Code</td>
                    <td style="border-top:1px solid #000;border-bottom:1px solid #000; font-weight:bold" width="28%">Item Description</td>
                    <td style="border-top:1px solid #000;border-bottom:1px solid #000; font-weight:bold" width="10%">Location</td>
                    <td style="border-top:1px solid #000;border-bottom:1px solid #000; font-weight:bold" width="10%">Is Pickup</td>
                    <td style="border-top:1px solid #000;border-bottom:1px solid #000; font-weight:bold" width="10%">vendor</td>
                    <td style="border-top:1px solid #000;border-bottom:1px solid #000; font-weight:bold" width="10%">Ven. Item</td>
                    <td style="border-top:1px solid #000;border-bottom:1px solid #000; font-weight:bold;text-align:right" width="10%">Quantity</td>
                    <td style="border-top:1px solid #000;border-bottom:1px solid #000; font-weight:bold;text-align:right" width="12%">Return Qty</td>
                  </tr>
                  '.$html_data.'
                  <tr><td colspan="8">&nbsp;</td></tr>
                  <tr>
                    <td colspan="6" style="border-top:1px solid #000;border-bottom:1px solid #000;">Total</td>
                    <td style="border-top:1px solid #000;border-bottom:1px solid #000;text-align:right;">'.$productsQty.'</td>
                    <td style="border-top:1px solid #000;border-bottom:1px solid #000;">&nbsp;</td>
                  </tr>
                  <tr>
                    <td colspan="4">&nbsp;</td>
                    <td colspan="4" style="line-height:50px;">Received all the above items in good condition</td>
                    </tr>
                  <tr>
                    <td colspan="2" valign="top" style="border-top:1px solid #000;padding:10px 0;">
                    <table width="100%">
                        <tr>
                            <td><strong>Stores-in-charge</strong></td>
                        </tr>
                        <tr>
                            <td>Driver Name</td>
                        </tr>
                        <tr>
                            <td>Helper</td>
                        </tr>
                        <tr>
                            <td>Carpenter</td>
                        </tr>
                    </table>
                </td>
                    <td colspan="4">&nbsp;</td>
                    <td colspan="2" valign="top" style="border-top:1px solid #000;padding:10px 0;text-align:center;">
                    <strong>Customer</strong></td>
                    </tr>
                </table></td>
              </tr>
            </table>
            </body>
            </html>
            ' ;
            return $html;

        }


}
