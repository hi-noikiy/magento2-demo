<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksula\EmailTemplate\Controller\Onepage;
use Magento\Framework\App\Action\Context;

class Failure extends \Magento\Checkout\Controller\Onepage
{
    /**
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $lastQuoteId = $this->getOnepage()->getCheckout()->getLastQuoteId();
        $lastOrderId = $this->getOnepage()->getCheckout()->getLastOrderId(); 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderObj = $objectManager->get('\Magento\Sales\Model\Order')->load($lastOrderId); 
        $increment_id = $orderObj->getIncrementId();

        $scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');

        $order_data = $orderObj->getData();
        if (!$lastQuoteId || !$lastOrderId) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
           
        $receiverInfo = [
            'name' => $order_data['customer_firstname']." ".$order_data['customer_lastname'],
            'email' => $order_data['customer_email']
        ];   

        $emailTempVariables = array();
        $emailTempVariables['name'] = $order_data['customer_firstname']." ".$order_data['customer_lastname'];
        $emailTempVariables['order_id'] = $increment_id;
        

        $domain_name =  $scopeConfig->getValue('sms_configuration/sms_setting/domain_name');
        $domain_email_id =  $scopeConfig->getValue('sms_configuration/sms_setting/domain_email_id');            
         
        $senderInfo = [
            'name' => $domain_name,
            'email' => $domain_email_id,
        ];
         
        $templateId = 'order_failure';  
        $templateIdAdmin = 'order_failure_admin';                  
            
        $objectManager->get('Iksula\EmailTemplate\Helper\Email')->emailTemplate($templateId,$emailTempVariables,$senderInfo,$receiverInfo,'','');

        $objectManager->get('Iksula\EmailTemplate\Helper\Email')->emailTemplate($templateIdAdmin,$emailTempVariables,$senderInfo,$senderInfo,'','');


        return $this->resultPageFactory->create();
    }
}
