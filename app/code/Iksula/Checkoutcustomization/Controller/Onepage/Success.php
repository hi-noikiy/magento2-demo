<?php
namespace Iksula\Checkoutcustomization\Controller\Onepage;

class Success extends \Magento\Checkout\Controller\Onepage
{


    public function execute()
    {

        $session = $this->getOnepage()->getCheckout();



          //$this->OrderSplitLogicHelper->OrdersplitOfOrders($session->getLastOrderId());

        if (!$this->_objectManager->get('Magento\Checkout\Model\Session\SuccessValidator')->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
        $session->clearQuote();
        //@todo: Refactor it to match CQRS

        /********** Ordersplit when success controller is called ***********/

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $ordersplitlogicHelper = $objectManager->get('\Iksula\Ordersplit\Helper\Ordersplitlogic');
        $ordersplitlogicHelper->OrdersplitOfOrders($session->getLastOrderId());
        /******************************************************************/

        $resultPage = $this->resultPageFactory->create();
        $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            ['order_ids' => [$session->getLastOrderId()]]
        );
        return $resultPage;
    }
}
