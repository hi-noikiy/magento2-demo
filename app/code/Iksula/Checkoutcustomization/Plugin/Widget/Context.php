<?php
namespace Iksula\Checkoutcustomization\Plugin\Widget;


class Context
{

    protected $request;

    protected $urlBuider;

    protected $orderRepository;


    public function __construct(\Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\UrlInterface $urlBuilder
        ) {
          $this->request = $request;
          $this->urlBuilder = $urlBuilder;
          $this->orderRepository = $orderRepository;
    }

    public function getIddata(){
        $this->request->getParams();
        return $this->request->getParam('order_id');
    }

    public function getPaymentmethod(){
        $order_id =  $this->getIddata();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if($order_id){
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($order_id);
            $payment = $order->getPayment()->getMethod();
            return $payment;
        }

    }

    public function getOrderState($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $state = $order->getState(); //Get Order State(Complete, Processing, ....)
        return $state;
    }

    public function getOrderStatus($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $state = $order->getStatus(); //Get Order State(Complete, Processing, ....)
        return $state;
    }

    public function afterGetButtonList(\Magento\Backend\Block\Widget\Context $subject,$buttonList){


        $paymentmethodname =  $this->getPaymentmethod();

        $order_id =  $this->getIddata();
        if($order_id){


        $cash_received_url = $this->urlBuilder->getUrl('checkoutcustomization/changestatus/cashreceived', $paramsHere = array()).'order_id/'.$order_id.'/';
        $cheque_cleared_url = $this->urlBuilder->getUrl('checkoutcustomization/changestatus/chequecleared', $paramsHere = array()).'order_id/'.$order_id.'/';
        $delivered_status_url  = $this->urlBuilder->getUrl('checkoutcustomization/changestatus/deliveredorder', $paramsHere = array()).'order_id/'.$order_id.'/';
        $aDeliveredStatusOrderenable = array('processing' , 'cash_received' , 'cheque_cleared');

        $orderstate = trim($this->getOrderState($order_id));
        $orderstatus = trim($this->getOrderStatus($order_id));
        if($orderstate=="new" && $orderstatus  !="processing" ){

            if($paymentmethodname =='cashondelivery'){
                if($subject->getRequest()->getFullActionName() == 'sales_order_view'){
                    $buttonList->add(
                        'cash_recieved',
                        [
                            'label' => __('Cash Recieved'),
                            'onclick' => "setLocation('".$cash_received_url."')",
                            'class' => 'ship'
                        ]
                    );
                }

            }
        }
        if($orderstate=="new" && $orderstatus!=="cheque_received" && $orderstate !="processing" ){
            if($paymentmethodname =='checkmo'){
                if($subject->getRequest()->getFullActionName() == 'sales_order_view'){
                    $buttonList->add(
                        'cheque_received',
                        [
                            'label' => __('Cheque Recieved'),
                            // 'onclick' => "setLocation('window.location.href')",
                            'onclick' => "openChequeFormPopup()",
                            'class' => 'ship'
                        ]
                    );
                }

            }
        }

        if($orderstate=="new" && $orderstate !=="processing"  ){
            if($orderstatus=="cheque_received"){

                if($paymentmethodname =='checkmo'){
                    if($subject->getRequest()->getFullActionName() == 'sales_order_view'){
                        $buttonList->add(
                            'cheque_cleared',
                            [
                                'label' => __('Cheque Cleared'),
                                 'onclick' => "setLocation('".$cheque_cleared_url."')",
                                'class' => 'ship'
                            ]
                        );
                    }

                }
            }
        }



        if((in_array($orderstatus , $aDeliveredStatusOrderenable)) && (($orderstatus!="delivered") && ($orderstatus!="complete")) || ($orderstate == 'processing')){

          $buttonList->add(
              'delivered',
              [
                  'label' => __('Delivered'),
                  // 'onclick' => "setLocation('window.location.href')",
                  // 'onclick' => "setLocation('".$delivered_status_url."')",
                  'onclick' => "checkUnallocatedOrders('".$order_id."')",
                  'class' => 'ship'
              ]
          );
        }


        if($orderstatus=="delivered"){

          $buttonList->add(
              'complete_order',
              [
                  'label' => __('Complete Order'),
                  // 'onclick' => "setLocation('window.location.href')",
                  'onclick' => "openEmiratesIdPopup()",
                  'class' => 'ship'
              ]
          );
        }

        $buttonList->remove('order_edit');

        }

        return $buttonList;
    }
}
