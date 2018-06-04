<?php
namespace Iksula\Checkoutcustomization\Controller\Adminhtml\Changestatus;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;

class Unallocatedorders extends Action
{
   
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
    public function execute()
    {

        $this->request->getParams();
        $order_id = $this->request->getParam('order_id');

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        
        if($order_id){
            try {

              $orderSplitCollectionData = $this->ordersplitFactory->create()
                ->getCollection()
                ->addFieldToFilter('order_id', array('eq' => $order_id))
                ->addFieldToFilter('order_item_status','store_unallocated')
                ->getData();
                $orderdata = array();
                foreach($orderSplitCollectionData as $ordersplitData){
                                           
                  $ordersplitObj = $this->ordersplitFactory->create()->load($ordersplitData['id']);
                  //$orderdata['order_item_id'] = $ordersplitData['order_item_id'];
                  array_push($orderdata, $ordersplitData['order_item_id']);
                  
                }
                $unallocatedorder = implode(',', $orderdata);
                if($unallocatedorder != ''){
                    $response = array(
                      "status" => (bool)true,
                      "orders" => $unallocatedorder
                    );
                }else{
                    $response = array(
                      "status" => (bool)false,
                      "orders" => ""
                    );
                }

            } catch (Exception $e) {
                $response = array(
                  "status" => (bool)false
                );
            }
        }
        $resultJson->setData($response);
        return $resultJson;
    }
}
