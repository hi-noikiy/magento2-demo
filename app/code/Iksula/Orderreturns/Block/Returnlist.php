<?php
namespace Iksula\Orderreturns\Block;
class Returnlist extends \Magento\Framework\View\Element\Template
{
    protected $request;
    protected $orderFactoryData;
    protected $orderreturn;
    protected $_customerSession;

	public function __construct(\Magento\Framework\View\Element\Template\Context $context,
            \Magento\Framework\App\Request\Http $request,
            \Magento\Sales\Model\OrderFactory  $orderFactoryData,
            \Magento\Customer\Model\Session $customerSession,
            \Iksula\Orderreturns\Model\OrderreturnFactory  $orderreturn)
	{
		parent::__construct($context);
        $this->request = $request;
        $this->orderFactoryData = $orderFactoryData;
        $this->_customerSession = $customerSession;
        $this->orderreturn = $orderreturn;
	}

	public function getReturnList(){
        $customer_id = $this->_customerSession->getCustomer()->getId(); 
        $return_reason = $this->orderreturn->create()->getCollection()
                            ->addFieldToFilter('customer_id',array('eq'=> $customer_id))                        
                            ->getData();

        return $return_reason;
    }
}