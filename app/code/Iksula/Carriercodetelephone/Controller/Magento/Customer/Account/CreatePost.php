<?php

namespace Iksula\Carriercodetelephone\Controller\Magento\Customer\Account;



use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Helper\Address;
use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Customer\Model\Registration;
use Magento\Framework\Escaper;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;






class CreatePost extends \Magento\Customer\Controller\Account\CreatePost
{


	/** @var AccountManagementInterface */
	protected $accountManagement;

	/** @var Address */
	protected $addressHelper;

	/** @var FormFactory */
	protected $formFactory;

	/** @var SubscriberFactory */
	protected $subscriberFactory;

	/** @var RegionInterfaceFactory */
	protected $regionDataFactory;

	/** @var AddressInterfaceFactory */
	protected $addressDataFactory;

	/** @var Registration */
	protected $registration;

	/** @var CustomerInterfaceFactory */
	protected $customerDataFactory;

	/** @var CustomerUrl */
	protected $customerUrl;

	/** @var Escaper */
	protected $escaper;

	/** @var CustomerExtractor */
	protected $customerExtractor;

	/** @var \Magento\Framework\UrlInterface */
	protected $urlModel;

	/** @var DataObjectHelper  */
	protected $dataObjectHelper;

	/**
	 * @var Session
	 */
	protected $session;

	/**
	 * @var AccountRedirect
	 */
	private $accountRedirect;

	/**
	 * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
	 */
	private $cookieMetadataFactory;

	/**
	 * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
	 */
	private $cookieMetadataManager;

	/**
	 * @param Context $context
	 * @param Session $customerSession
	 * @param ScopeConfigInterface $scopeConfig
	 * @param StoreManagerInterface $storeManager
	 * @param AccountManagementInterface $accountManagement
	 * @param Address $addressHelper
	 * @param UrlFactory $urlFactory
	 * @param FormFactory $formFactory
	 * @param SubscriberFactory $subscriberFactory
	 * @param RegionInterfaceFactory $regionDataFactory
	 * @param AddressInterfaceFactory $addressDataFactory
	 * @param CustomerInterfaceFactory $customerDataFactory
	 * @param CustomerUrl $customerUrl
	 * @param Registration $registration
	 * @param Escaper $escaper
	 * @param CustomerExtractor $customerExtractor
	 * @param DataObjectHelper $dataObjectHelper
	 * @param AccountRedirect $accountRedirect
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
	 */


	public function __construct(
	Context $context,
	Session $customerSession,
	ScopeConfigInterface $scopeConfig,
	StoreManagerInterface $storeManager,
	AccountManagementInterface $accountManagement,
	Address $addressHelper,
	UrlFactory $urlFactory,
	FormFactory $formFactory,
	SubscriberFactory $subscriberFactory,
	RegionInterfaceFactory $regionDataFactory,
	AddressInterfaceFactory $addressDataFactory,
	CustomerInterfaceFactory $customerDataFactory,
	CustomerUrl $customerUrl,
	Registration $registration,
	Escaper $escaper,
	CustomerExtractor $customerExtractor,
	DataObjectHelper $dataObjectHelper,
	AccountRedirect $accountRedirect){


		$this->session = $customerSession;
		$this->scopeConfig = $scopeConfig;
		$this->storeManager = $storeManager;
		$this->accountManagement = $accountManagement;
		$this->addressHelper = $addressHelper;
		$this->formFactory = $formFactory;
		$this->subscriberFactory = $subscriberFactory;
		$this->regionDataFactory = $regionDataFactory;
		$this->addressDataFactory = $addressDataFactory;
		$this->customerDataFactory = $customerDataFactory;
		$this->customerUrl = $customerUrl;
		$this->registration = $registration;
		$this->escaper = $escaper;
		$this->customerExtractor = $customerExtractor;
		$this->urlModel = $urlFactory->create();
		$this->dataObjectHelper = $dataObjectHelper;
		$this->accountRedirect = $accountRedirect;
		parent::__construct(
			$context,
			$customerSession,
			$scopeConfig,
			$storeManager,
			$accountManagement,
			$addressHelper,
			$urlFactory,
			$formFactory,
			$subscriberFactory,
			$regionDataFactory,
			$addressDataFactory,
			$customerDataFactory,
			$customerUrl,
			$registration,
			$escaper,
			$customerExtractor,
			$dataObjectHelper,
			$accountRedirect

	);

	}

	private function getCookieManager()
	{
			if (!$this->cookieMetadataManager) {
					$this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
							\Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
					);
			}
			return $this->cookieMetadataManager;
	}


	private function getCookieMetadataFactory()
	{
			if (!$this->cookieMetadataFactory) {
					$this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
							\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
					);
			}
			return $this->cookieMetadataFactory;
	}


	public function execute()
	{
		/** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();
		if ($this->session->isLoggedIn() || !$this->registration->isAllowed()) {
				$resultRedirect->setPath('*/*/');
				return $resultRedirect;
		}

		if (!$this->getRequest()->isPost()) {
				$url = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
				$resultRedirect->setUrl($this->_redirect->error($url));
				return $resultRedirect;
		}

		$this->session->regenerateId();

		try {
				$address = $this->extractAddress();
				$addresses = $address === null ? [] : [$address];

				$customer = $this->customerExtractor->extract('customer_account_create', $this->_request);
				$customer->setAddresses($addresses);

				$password = $this->getRequest()->getParam('password');
				$confirmation = $this->getRequest()->getParam('password_confirmation');
				$redirectUrl = $this->session->getBeforeAuthUrl();

				$this->checkPasswordConfirmation($password, $confirmation);

				$customer = $this->accountManagement
						->createAccount($customer, $password, $redirectUrl);

				if ($this->getRequest()->getParam('is_subscribed', false)) {
						$this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
				}

				$this->_eventManager->dispatch(
						'customer_register_success',
						['account_controller' => $this, 'customer' => $customer]
				);

				$confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
				if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
						$email = $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());
						// @codingStandardsIgnoreStart
						$this->messageManager->addSuccess(
								__(
										'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.',
										$email
								)
						);
						// @codingStandardsIgnoreEnd
						$url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
						$resultRedirect->setUrl($this->_redirect->success($url));
				} else {
						$this->session->setCustomerDataAsLoggedIn($customer);
						$this->messageManager->addSuccess($this->getSuccessMessage());
						$requestedRedirect = $this->accountRedirect->getRedirectCookie();
						if (!$this->scopeConfig->getValue('customer/startup/redirect_dashboard') && $requestedRedirect) {
								$resultRedirect->setUrl($this->_redirect->success($requestedRedirect));
								$this->accountRedirect->clearRedirectCookie();
								return $resultRedirect;
						}
						$resultRedirect = $this->accountRedirect->getRedirect();
				}
				if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
						$metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
						$metadata->setPath('/');
						$this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
				}

				return $resultRedirect;
		} catch (StateException $e) {
				$url = $this->urlModel->getUrl('customer/account/forgotpassword');
				// @codingStandardsIgnoreStart
				$message = __(
						'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
						$url
				);
				// @codingStandardsIgnoreEnd
				$this->messageManager->addError($message);
		} catch (InputException $e) {
				$this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
				foreach ($e->getErrors() as $error) {
						$this->messageManager->addError($this->escaper->escapeHtml($error->getMessage()));
				}
		} catch (LocalizedException $e) {
				$this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
		} catch (\Exception $e) {
				$this->messageManager->addException($e, __('We can\'t save the customer.'));
		}

		$this->session->setCustomerFormData($this->getRequest()->getPostValue());
		$defaultUrl = $this->urlModel->getUrl('*/*/login', ['_secure' => true]);
		$resultRedirect->setUrl($this->_redirect->error($defaultUrl));
		return $resultRedirect;
	}


}
