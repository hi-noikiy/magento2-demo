<?php
namespace Iksula\Orderreturns\Block;
class Returns extends \Magento\Framework\View\Element\Template
{
    protected $request;
    protected $orderFactoryData;
    protected $returnreasonFactoryData;
    protected $_countryFactory;
    protected $_ordersplitFactory;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context,
            \Magento\Framework\App\Request\Http $request,
            \Magento\Sales\Model\OrderFactory  $orderFactoryData,
            \Magento\Directory\Model\CountryFactory $countryFactory,
            \Iksula\Orderreturns\Model\ReturnreasonFactory  $returnreasonFactoryData,
            \Iksula\Ordersplit\Model\Ordersplits $ordersplitFactory)
    {
        parent::__construct($context);
        $this->request = $request;
        $this->orderFactoryData = $orderFactoryData;
        $this->_countryFactory = $countryFactory;
        $this->returnreasonFactoryData = $returnreasonFactoryData;
        $this->_ordersplitFactory       = $ordersplitFactory;
    }

    public function getSubOrderDetails()
    {

        $return_chk = $this->request->getParam('return_chk');
        $returnChkArr = explode('--', $return_chk); 
        $product_id = $returnChkArr[0];
        $order_id = $returnChkArr[1]; 
        $returnarray = array();      
            if($order_id){
                $order = $this->orderFactoryData->create()->loadByIncrementId($order_id);
                $orderEntityId = $order->getEntityId();                
                $orderItems = $order->getAllItems();
               foreach ($orderItems as $item) {
                    if($item->getProductId()==$product_id){
                        $itemQty = $item->getQtyOrdered();                       
                        if($item->getOriginalPrice()==$item->getPrice()){
                            $originalPrice = $item->getOriginalPrice(); 
                            $Price = $item->getPrice();
                        }else{
                            $originalPrice = $item->getOriginalPrice();
                            $Price = $item->getPrice();
                        }
                        $item_order_id = $item->getOrderId();
                        $sku = $item->getSku();
                        $ordersplit_collection = $this->_ordersplitFactory->getCollection()
                          ->addFieldToFilter('order_id',array('eq'=>$item_order_id))
                          ->addFieldToFilter('order_item_status','delivered')
                          ->addFieldToSelect('order_items_data')
                          ->getData()
                          ;
                        $totalinventory = 0;
                        foreach ($ordersplit_collection as $value1) {
                            $order_items_data = $value1['order_items_data'];
                            $order_items_data_json = json_decode($order_items_data);
                            foreach ($order_items_data_json as $value2) {
                                $value2array = (array)$value2;
                                $splitSku = $value2array['sku'];                  
                                $inventory = $value2array['inventory'];
                                if($splitSku == $sku){
                                    $totalinventory +=$inventory;                            
                                }
                            }
                        }
                        $returnarray=array('product_id'=>$item->getProductId(),
                                        'product_name'=>$item->getName(),
                                        'product_qty'=>(int)$totalinventory,
                                        'product_sku'=>$item->getSku(),
                                        'order_id'=>$order_id,
                                        'order_entity_id'=>$orderEntityId,
                                        'product_price'=>(int)$originalPrice,
                                        'return_price'=>(int)$Price);

                    }
                }                
            
            }          
        return $returnarray;
    }
    public function getReturnReason(){
        $return_reason = $this->returnreasonFactoryData->create()->getCollection()
                        ->addFieldToSelect('id')
                        ->addFieldToSelect('return_reason')
                        ->addFieldToFilter('status',array('eq'=>0))
                        ->getData();
        return $return_reason;
    }

    public function getShippingAddressDetails($order_id){
        
        $order = $this->orderFactoryData->create()->loadByIncrementId($order_id);
        $shippingAddressObj = $order->getShippingAddress();        
        return $shippingAddressObj->getData();
    }
    
    public function getPaymentDetails($order_id){
        $order = $this->orderFactoryData->create()->loadByIncrementId($order_id);
        $paymentType = $order->getPayment()->getMethodInstance()->getTitle();        
        return $paymentType;
    }   
}