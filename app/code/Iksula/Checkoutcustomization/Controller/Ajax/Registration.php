<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Iksula\Checkoutcustomization\Controller\Ajax;

use Magento\Framework\App\Action\Action;
// use Magento\Customer\Model\Account\Redirect as AccountRedirect;
// use Magento\Customer\Api\Data\AddressInterface;
// use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Model\Session;
// use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
// use Magento\Customer\Api\AccountManagementInterface;
// use Magento\Customer\Helper\Address;
use Magento\Framework\UrlFactory;
// use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Newsletter\Model\SubscriberFactory;
// use Magento\Customer\Api\Data\RegionInterfaceFactory;
// use Magento\Customer\Api\Data\AddressInterfaceFactory;
// use Magento\Customer\Api\Data\CustomerInterfaceFactory;
// use Magento\Customer\Model\Url as CustomerUrl;
// use Magento\Framework\Escaper;
// use Magento\Customer\Model\CustomerExtractor;


class Registration extends Action
{


    // protected $accountManagement;
    // protected $addressFactory;
    // protected $regionFactory;
    // protected $orderRepository;
    // protected $objectCopyService;

    protected $customerFactory;
    protected $resultJsonFactory;
    protected $session;
    // protected $registration;
    protected $urlModel;
    protected $subscriberFactory;
    private $customer;
    protected $_customerSession;
    protected $resultRedirectFactory;
    protected $_messageManager;

    protected $_url;


    // \Magento\Framework\UrlInterface $url,
    // \Magento\Framework\Message\ManagerInterface $messageManager



         // Session $customerSession,
         // Registration $registration,
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
         UrlFactory $urlFactory,
         SubscriberFactory $subscriberFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Customer $customer,
        Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\Redirect $resultRedirectFactory
    )
    {
        // $this->session = $customerSession;
        // $this->registration = $registration;
        $this->storeManager     = $storeManager;
        $this->customerFactory  = $customerFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->urlModel = $urlFactory->create();
        $this->subscriberFactory = $subscriberFactory;
        $this->customer = $customer;
        $this->_customerSession = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->_messageManager = $messageManager;
        // $this->_url = $url;
        // $this->_messageManager = $messageManager;
        parent::__construct($context);

    }

    public function execute()
    {

        // $resultRedirect = $this->resultRedirectFactory->create();
        // if ($this->session->isLoggedIn() || !$this->registration->isAllowed()) {
        //     $resultRedirect->setPath('*/*/');
        //     return $resultRedirect;
        // }

        // if (!$this->getRequest()->isPost()) {
        //     $url = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
        //     $resultRedirect->setUrl($this->_redirect->error($url));
        //     return $resultRedirect;
        // }
        try {


            $customerData = $this->getRequest()->getParams();

            $firstname = $this->getRequest()->getParam( 'firstname');
            $lastname = $this->getRequest()->getParam('lastname');
            $email = $this->getRequest()->getParam('email');
            $password = $this->getRequest()->getParam('password');
            $password_confirmation = $this->getRequest()->getParam('password_confirmation');
            $country_code = $this->getRequest()->getParam('country_code');
            $carrier_code = $this->getRequest()->getParam('carrier_code');
            $nationality = $this->getRequest()->getParam('nationality');
            $gender =  $this->getRequest()->getParam('gender');


            $subscribe =  $this->getRequest()->getParam('is_subscribed');
            // $this->session->regenerateId();
            $websiteId  = $this->storeManager->getWebsite()->getWebsiteId();

            $checkpassword = $this->checkPasswordConfirmation($password, $password_confirmation);
            $checkcustomerexits = $this->customerExists($email, $websiteId);
            
            if($checkpassword==true){
                $response = [
                                'errors' => true,
                                'message' => __('Password Mismatch')
                            ];
                $this->_messageManager->addError('Password Mismatch');
            }elseif($checkcustomerexits==1 || $checkcustomerexits==true){
                $response = [
                                'errors' => true,
                                'message' => __('Customer already exists')
                            ];
                $this->_messageManager->addError('Customer already exists');
            }else{
                 // Get Website ID

                // Instantiate object (this is the most important part)
                $customer   = $this->customerFactory->create();
                $customer->setWebsiteId($websiteId);

                // Preparing data for new customer

                $customer->setEmail($email);
                $customer->setFirstname($firstname);
                $customer->setLastname($lastname);
                $customer->setPassword($password);
                //$customer->setAccountTelephone('99999999');
                //$customer->setTeleNumber('9999999999');
                // $customer->setCountryCode($country_code);
                // $customer->setCarrierCode($carrier_code);
                $customer->setNationality($nationality);
                $customer->setGender($gender);

                // Save data
                $customer->save();
                $customer->sendNewAccountEmail();

                if ((int)$subscribe==1) {
                        $this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
                }
                $response = [
                    'errors' => false,
                    'message' => __('Customer Registration successful.')
                ];

                

                $this->_customerSession->setCustomerAsLoggedIn($customer);
            }
             
            
            //$this->getResponse()->setBody(Zend_Json::encode($response));

            // $resultJson = $this->resultJsonFactory->create();
            // return $resultJson->setData($response);
        } catch (Exception $e) {
            $response = [
                                'errors' => true,
                                'message' => __('Something went wrong. Please try again')
                            ];
        }

        if(!$response['errors']){

          // $checkouturl = $this->_url->getUrl('checkout');
          // $this->_responseFactory->create()->setRedirect($checkouturl)->sendResponse();
          // $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
          // $resultRedirect->setUrl($this->_redirect->getRefererUrl());
          return $this->resultRedirectFactory->create()->setPath('checkout/', ['_current' => true]);
          exit;

        }else{
          // $this->_messageManager->addErrorMessage($response['message']);
          // $redirectUrl = $this->_redirect->getRefererUrl();
          // $this->_responseFactory->create()->setRedirect($redirectUrl)->sendResponse();

          return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRefererUrl());
          exit;

          // $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
          // $resultRedirect->setUrl($this->_redirect->getRefererUrl());
          // exit;
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);

    }

    protected function checkPasswordConfirmation($password, $confirmation)
    {
        if ($password != $confirmation) {
            return true;
        }
    }

    public function customerExists($email, $websiteId = null)
    {
        $customer = $this->customer;
        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
        // echo "Aaa";exit();
            return true;
        }else{
            // echo "bbb";exit();
            return false;
        }

    }

}
