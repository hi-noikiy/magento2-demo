<?php
namespace Iksula\Checkoutcustomization\Controller\Adminhtml\Changestatus;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;

class Deliveredorder extends Action
{
    const ADMIN_RESOURCE = 'Iksula_Checkoutcustomization::changestatus';

    const SALES_REP_EMAIL = 'trans_email/ident_sales/email';

    const STORE_REP_NAME = 'trans_email/ident_sales/name';

    const CHEQUE_CLEARED_NOTIFICATION = '2XL Cheque cleared Notification';


    const DELIVERED_STATE = 'delivered';

    const DELIVERED_STATUS = 'delivered';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $request;

    protected $_storeManager;

    protected $_transportBuilder;

    protected $_scopeConfig;

    protected $ordersplitFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
        , \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->_storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_customers = $customer;
        $this->_scopeConfig = $scopeConfig;
        $this->ordersplitFactory = $ordersplitFactory;

    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */

    public function getCollection()
    {
        //Get customer collection
        return $this->_customers->getCollection();
    }

    public function getCustomer($customerId)
    {
        //Get customer by customerID
        return $this->_customers->load($customerId);
    }


    public function getSalesRepresentativeEmail() {
         $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
         return $this->_scopeConfig->getValue(self::SALES_REP_EMAIL, $storeScope); //you get your value here
    }

    public function getSalesRepresentativeName() {
         $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
         return $this->_scopeConfig->getValue(self::STORE_REP_NAME, $storeScope); //you get your value here
    }


    public function execute()
    {


        $this->request->getParams();
        $order_id = $this->request->getParam('order_id');

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if($order_id){
            try {

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $order = $objectManager->create('\Magento\Sales\Model\Order')->load($order_id);
                $orderState = Order::STATE_COMPLETE;
                $orderStatus = SELF::DELIVERED_STATUS;
                $order->setState($orderState)->setStatus($orderStatus);
                $order->save();
                 $orderSplitCollectionData = $this->ordersplitFactory->create()
                                          ->getCollection()
                                          ->addFieldToFilter('order_id', array('eq' => $order_id))
                                          ->addFieldToFilter('order_item_status','store_shipped')
                                          ->getData();

                                          foreach($orderSplitCollectionData as $ordersplitData){
                                            if($this->checkIfOrderItemCancelled($ordersplitData['id'])){
                                              continue;
                                            }
                                                $ordersplitObj = $this->ordersplitFactory->create()->load($ordersplitData['id']);
                                                $ordersplitObj->setOrderItemStatus($orderStatus);
                                                $ordersplitObj->save();
                                          }

            } catch (Exception $e) {
                
            }
            $order->getCustomerEmail();
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
       return $resultPage;
    }

    public function checkIfOrderItemCancelled($row_id){

        $order_item_status = $this->ordersplitFactory->create()->load($row_id)->getOrderItemStatus();
        if($order_item_status == 'store_cancelled'){
            return true;
        }else{
          return false;
        }

    }


}
