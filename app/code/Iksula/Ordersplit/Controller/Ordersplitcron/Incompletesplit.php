<?php

namespace Iksula\Ordersplit\Controller\Ordersplitcron;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Incompletesplit extends Action {


  const SALES_REP_EMAIL = 'trans_email/ident_sales/email';
  const CRON_RECEIVER_EMAIL = 'ordersplit_cron/setting/email';

  protected $_orderCollectionFactory;
  protected $_ordersplitFactory;
  protected $_emailHelper;
  protected $_scopeConfig;

  public function __construct(
    Context $context,
    \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
    \Iksula\Ordersplit\Model\Ordersplits $ordersplitFactory, 
    \Iksula\EmailTemplate\Helper\Email $emialHepler,    
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    array $data = []
  )
  {
    $this->_orderCollectionFactory  = $orderCollectionFactory;
    $this->_ordersplitFactory       = $ordersplitFactory;   
    $this->_emailHelper             = $emialHepler;
    $this->_scopeConfig             = $scopeConfig;   
    parent::__construct($context, $data);   
  }

    public function execute() {
        echo "current_date - ".$current_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d H:i:s');
        echo " prev_date - ".$prev_date = date('Y-m-d H:i:s', strtotime($current_date .' -1 day'));
        echo "<pre>";
        
        $orders_collection = $this->_orderCollectionFactory->create()
                          ->addFieldToFilter('created_at', array('from'=>$prev_date, 'to'=>$current_date))
                          ->addFieldToFilter('ordersplit_status',array('eq'=>1))
                          ->addFieldToSelect('increment_id')
                          ->addFieldToSelect('total_qty_ordered')
                          ->addFieldToSelect('entity_id')
                          ;

        $orderData = $orders_collection->getData();
        // print_r($orderData);
        $orderIds = array();
        $incompletesplittedOrders = "";
        $orderIncrementIds = array();                              
        foreach ($orderData as $value) {
            // echo "<br> increment_id - ".$value['increment_id']; 
            // echo " totalQty - ".$totalQty = $value['total_qty_ordered']; 
            $totalQty = $value['total_qty_ordered']; 
            // $ordersplit_collection = $this->_ordersplitFactory->create()
            $ordersplit_collection = $this->_ordersplitFactory->getCollection()
                          ->addFieldToFilter('order_id',array('eq'=>$value['entity_id']))
                          ->addFieldToSelect('order_items_data')
                          ->getData()
                          ;     
            $totalinventory = 0;
            foreach ($ordersplit_collection as $value1) {
                $order_items_data = $value1['order_items_data'];
                $order_items_data_json = json_decode($order_items_data);              
                foreach ($order_items_data_json as $value2) {                  
                    $value2array = (array)$value2;
                    $inventory = $value2array['inventory'];
                    $totalinventory +=$inventory;                            
                }
            }                          
            if($totalinventory!=$totalQty){
              array_push($orderIncrementIds,$value['increment_id']);
            }            
        }
        echo "<br> incompletesplittedOrders  - ".$incompletesplittedOrders = implode(',',$orderIncrementIds); 

        $receiverInfo = [
              'name' => 'Arman Khan',
              'email' => $this->getReceiverEmail(),
            ];

            /* Sender Detail  */
        $senderInfo = [
          'name' => '2XL Home',
          'email' => $this->getSalesRepresentativeEmail(),
        ];

        $emailTemplateVariables = [
          'order_ids' => $incompletesplittedOrders,
        ];

        $templateId = 'incomplete_ordersplit_cron_log';

        try {
        	if($orderIncrementIds){        		
	        	$to_email = $this->getReceiverEmail();
				$subject = 'Order split pending reminder';
				$message = 'Orders, which are incompletely splitted : '.$incompletesplittedOrders;
				$headers = 'From: '.$this->getSalesRepresentativeEmail();
				mail($to_email,$subject,$message,$headers);
            	echo "Done  : email send";
        	}
            // $this->_emailHelper->emailTemplate($templateId,$emailTemplateVariables,$senderInfo,$receiverInfo,'',''); 
             exit;
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
