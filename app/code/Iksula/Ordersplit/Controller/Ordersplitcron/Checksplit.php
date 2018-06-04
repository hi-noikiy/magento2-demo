<?php

namespace Iksula\Ordersplit\Controller\Ordersplitcron;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Checksplit extends Action {


  const SALES_REP_EMAIL = 'trans_email/ident_sales/email';
  const CRON_RECEIVER_EMAIL = 'ordersplit_cron/setting/email';

  protected $_orderCollectionFactory;
  protected $_emailHelper;
  protected $_scopeConfig;

  public function __construct(
    Context $context,
    \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory, 
    \Iksula\EmailTemplate\Helper\Email $emialHepler,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    array $data = []
  )
  {
    $this->_orderCollectionFactory  = $orderCollectionFactory;
    $this->_emailHelper             = $emialHepler; 
    $this->_scopeConfig             = $scopeConfig;  
    parent::__construct($context, $data);   
  }

    public function execute() {
        echo "current_date - ".$current_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d H:i:s');
        echo " prev_date - ".$prev_date = date('Y-m-d H:i:s', strtotime($current_date .' -1 day'));
        
        $orders_collection = $this->_orderCollectionFactory->create()
                          ->addFieldToFilter('created_at', array('from'=>$prev_date, 'to'=>$current_date))
                          ->addFieldToFilter('ordersplit_status',array('eq'=>0))
                          ->addFieldToSelect('increment_id')
                          ->addFieldToFilter('state',array('eq'=>'processing'))
                          ;

        $orderData = $orders_collection->getData();
        $orderIds = array();
        foreach ($orderData as $value) {
            array_push($orderIds, $value['increment_id']);      
        }

        echo "unsplittedOrders - ".$unsplittedOrders = implode(',',$orderIds);  
        exit;
        // echo "aaaaa".$this->getReceiverEmail();
        // exit();
        $receiverInfo = [
              'name' => 'Sir',
              'email' => $this->getReceiverEmail(),
            ];

            /* Sender Detail  */
        $senderInfo = [
          'name' => '2XL Home',
          'email' => $this->getSalesRepresentativeEmail(),
        ];

        $emailTemplateVariables = [
          'order_ids' => $unsplittedOrders,
        ];

        $templateId = 'ordersplit_cron_log';

        try {
        	if($orderIds){        		
	        	$to_email = $this->getReceiverEmail();
				$subject = 'Order split pending reminder';
				$message = 'Orders, which are not splitted being processed : '.$unsplittedOrders;
				$headers = 'From: '.$this->getSalesRepresentativeEmail();
				mail($to_email,$subject,$message,$headers);
            	echo "Done  : email send";
        	}
            //$this->_emailHelper->emailTemplate($templateId,$emailTemplateVariables,$senderInfo,$receiverInfo,'',''); 

        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    public function getSalesRepresentativeEmail() {
         $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
         return $this->_scopeConfig->getValue(self::SALES_REP_EMAIL, $storeScope); //you get your value here
    }

    public function getReceiverEmail() {
         $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
         return $this->_scopeConfig->getValue(self::CRON_RECEIVER_EMAIL, $storeScope); //you get your value here
    }    

}
