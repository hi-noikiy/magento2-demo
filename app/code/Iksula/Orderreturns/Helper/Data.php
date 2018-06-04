<?php
namespace Iksula\Orderreturns\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
     protected $_registry;

	   protected $_orderreturn;
		/**
		* @var \Magento\Framework\App\Config\ScopeConfigInterface
		*/
	   	protected $scopeConfig;
	   	const XML_PATH_Expiry_Days = 'orderreturns/setting/enterdays';
	   	const XML_PATH_Message = 'orderreturns/setting/message';
	   public function __construct(\Magento\Framework\Registry $registry,
	   			\Iksula\Orderreturns\Model\OrderreturnFactory $orderreturn,
	   			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	   ){
	   		$this->_registry = $registry;
	   		$this->_orderreturn = $orderreturn;
			$this->scopeConfig = $scopeConfig;
	   }


	   public function getReturnStatus($product_id,$order_id){
	   		$return_reason = $this->_orderreturn->create()->getCollection()
	   						 ->addFieldToFilter('order_id',array('eq'=>$order_id))
	   						 ->addFieldToFilter('product_id',array('eq'=>$product_id))
	   						 ->addFieldToSelect('return_status')
	   						 ->getData()
	   						 ;
			if($return_reason){
				$return_reason_val = $return_reason[0]['return_status'];
				$return_reason_value = $this->getReturnStatusValue($return_reason_val);
	   			return $return_reason_value;
			}else{
				return "false";
			}
    }

     public function getReturnStatusValue($id){
        $value = $this->getReturnStatusArray();
        return $value[$id];

     }

     public function getReturnStatusArray()
     {
            $data_array=array();
      $data_array[0]='Return Pending';
      $data_array[1]='Return Pickup Schedule';
      $data_array[2]='Return Received';
      $data_array[3]='Refund via Store Credit';
      $data_array[4]='Refund via Card';
            return($data_array);
	   }
    /**
     * Get Maximum number of days to limit return policy
     *
     * @return int
     */
	public function getMaximumDaysForReturnPolicy(){
		return $this->scopeConfig->getValue(self::XML_PATH_Expiry_Days,
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
	}
    /**
     * Get Message from configuration
     *
     * @return varchar
     */
	public function getConfigMessage(){
		return $this->scopeConfig->getValue(self::XML_PATH_Message,
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
	}
}
