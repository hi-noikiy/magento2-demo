<?php
namespace Iksula\EmailTemplate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;


class Register implements ObserverInterface
{
    public function __construct(\Iksula\EmailTemplate\Helper\Email $email,\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig){   
        $this->email = $email;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        $accountController = $observer->getAccountController();
        $customer = $observer->getCustomer();
       
        $request = $accountController->getRequest();
        $customer_number = $request->getParam('account_telephone');
        $number = (int)str_replace("-","",$customer_number);
        $email = $customer->getEmail();
        $name = $customer->getFirstname().' '.$customer->getLastname();
        $newPasswordString = 'Password you set when creating account';
        $data = array(
            'name' => $name,
            'email' => $email,
            'password' => $newPasswordString,
            );

        $result['email'] =  $this->scopeConfig->getValue('sms_configuration/sms_setting/domain_email_id');
        $result['name'] = $this->scopeConfig->getValue('sms_configuration/sms_setting/domain_name');

        $is_enable =  $this->scopeConfig->getValue('customer/sms_templates/enable');
        $template_path =  $this->scopeConfig->getValue('customer/sms_templates/customer_sms');

        $receiver['email'] = $email;
        $receiver['name'] = $name;
        
        if($is_enable)
            $this->email->smsTemplate($template_path, $data, $number);
        
        //$this->email->emailTemplate('registration',$data ,$result , $receiver, '','');
    }
}