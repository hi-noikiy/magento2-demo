<?php

namespace Iksula\Ordersplit\Block\Adminhtml\Ordersplits\Edit\Tab;

/**
 * Ordersplits edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Iksula\Ordersplit\Model\Status
     */
    protected $_status;


    protected $authSession;

    protected $_ordersplitsFactory;

    protected $response;

    protected $_userFactory;

    private $roleFactory; 

    protected $storemanager;

    protected $messageManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Iksula\Ordersplit\Model\Status $status,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Authorization\Model\RoleFactory $roleFactory, 
        \Iksula\Ordersplit\Model\OrdersplitsFactory $OrdersplitsFactory,
        \Magento\Framework\App\Response\Http $response,
        \Iksula\Storemanager\Model\StoremanagerFactory $storemanager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        $this->_ordersplitsFactory = $OrdersplitsFactory;
        $this->_systemStore = $systemStore;
        $this->_userFactory = $userFactory;
        $this->roleFactory = $roleFactory;
        $this->_status = $status;
        $this->authSession = $authSession;
        $this->storemanager = $storemanager;
        $this->messageManager = $messageManager;
        $this->response = $response;
        parent::__construct($context, $registry, $formFactory, $data);
    }


    protected function getCurrentUser(){


        return $this->authSession->getUser();

    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Iksula\Ordersplit\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('ordersplits');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        

         $user_id = "";

        $user_id = $this->getCurrentUser()->getUserId();

        if(($user_id != "") && ($user_id != 1)){

            $allocated_storeids = $this->_ordersplitsFactory->create()->load($model->getId())->getAllocatedStoreids();
            $aAllocatedStoreIds = explode(',' , $allocated_storeids);

            $role_id = $this->_userFactory->create()->load($user_id)->getRole()->getRoleId();            
            

            $store_ids = $this->storemanager->create()->getCollection()->addFieldToSelect('storemanager_id')->addFieldToFilter('role_id_mapping' , array('eq' => $role_id))->getData();

            $storeIds = array_column($store_ids, 'storemanager_id');
             
            $iFirstStoreId = $storeIds[0];
                
                if(!in_array($iFirstStoreId, $aAllocatedStoreIds)){
                    $this->messageManager->addErrorMessage('Access Denied for accessing store');
                    $this->response->setRedirect($this->getUrl('ordersplit/ordersplits/index/'));
                }
                        
        }

		
        $fieldset->addField(
            'order_id',
            'text',
            [
                'name' => 'order_id',
                'label' => __('Order Id'),
                'title' => __('Order Id'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'order_item_id',
            'text',
            [
                'name' => 'order_item_id',
                'label' => __('Order Item Id'),
                'title' => __('Order Item Id'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'picklist_id',
            'text',
            [
                'name' => 'picklist_id',
                'label' => __('Pick List Id'),
                'title' => __('Pick List Id'),
				
                'disabled' => $isElementDisabled
            ]
        );
					

        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::MEDIUM
        );
        $timeFormat = $this->_localeDate->getTimeFormat(
            \IntlDateFormatter::MEDIUM
        );

        $fieldset->addField(
            'order_date',
            'date',
            [
                'name' => 'order_date',
                'label' => __('Order Date'),
                'title' => __('Order Date'),
                    'date_format' => $dateFormat,
                    //'time_format' => $timeFormat,
				
                'disabled' => $isElementDisabled
            ]
        );
						
						
						
        $fieldset->addField(
            'order_item_status',
            'text',
            [
                'name' => 'order_item_status',
                'label' => __('Order Item Status'),
                'title' => __('Order Item Status'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'order_status',
            'text',
            [
                'name' => 'order_status',
                'label' => __('Order Status'),
                'title' => __('Order Status'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'invoice',
            'text',
            [
                'name' => 'invoice',
                'label' => __('Invoice'),
                'title' => __('Invoice'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'shipment',
            'text',
            [
                'name' => 'shipment',
                'label' => __('Shipment'),
                'title' => __('Shipment'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'allocated_storeids',
            'text',
            [
                'name' => 'allocated_storeids',
                'label' => __('Allocated Store Id'),
                'title' => __('Allocated Store Id'),
				
                'disabled' => $isElementDisabled
            ]
        );
					

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);
		
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Item Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Item Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    
    public function getTargetOptionArray(){
    	return array(
    				'_self' => "Self",
					'_blank' => "New Page",
    				);
    }
}
