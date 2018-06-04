<?php

namespace Iksula\Storemanager\Block\Adminhtml\Storemanager\Edit\Tab;

/**
 * Storemanager edit form main tab
 */
class Usercredential extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Iksula\Storemanager\Model\Status
     */
    protected $_status;


    protected $storemanagerFactory;


    protected $roleFactory;


    protected $userFactory;

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
        \Iksula\Storemanager\Model\Status $status,
        \Iksula\Storemanager\Model\StoremanagerFactory $storemanagerFactory,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        \Magento\User\Model\UserFactory $userFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        $this->storemanagerFactory = $storemanagerFactory;
        $this->roleFactory = $roleFactory;
        $this->userFactory = $userFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Iksula\Storemanager\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('storemanager');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Store Manager')]);

        if ($model->getId()) {
            $fieldset->addField('storemanager_id', 'hidden', ['name' => 'storemanager_id']);
            $password_class = 'validate-admin-password';
            $confirm_passwordclass = "";
            $password_astrick = false;

            /*********************  Get user details using Role Details by Role Id Mapping columns ***********/

            $rolemappingId = $this->storemanagerFactory->create()->load($model->getId())->getRoleIdMapping();

            $Usercollection = $this->roleFactory->create()->load($rolemappingId , 'parent_id');

            $user_id = $Usercollection->getUserId();

            $userfactoryCollection = $this->userFactory->create()->load($user_id);
            $firstname = $userfactoryCollection->getFirstName();
            $lastname = $userfactoryCollection->getLastName();

            /************************************************************/

            $model->setData('first_name' , $firstname);
            $model->setData('last_name' , $lastname);
            
        }else{

            $password_class = 'required-entry validate-admin-password';
            $password_astrick = true;
            $confirm_passwordclass = 'validate-cpassword';
        }

		
        $fieldset->addField(
            'first_name',
            'text',
            [
                'name' => 'first_name',
                'label' => __('First Name'),
                'title' => __('First Name'),
                'required' => true,                
                'class' => 'required-entry',                
				
                'disabled' => $isElementDisabled,                
            ]
        );
					
        $fieldset->addField(
            'last_name',
            'text',
            [
                'name' => 'last_name',
                'label' => __('Last Name'),
                'title' => __('Last Name'),
                'required' => true,                
                'class' => 'required-entry',                
				
                'disabled' => $isElementDisabled
            ]
        );					
        
					
        $fieldset->addField(
            'password',
            'password',
            [
                'name' => 'password',
                'label' => __('Password'),
                'title' => __('Password'),                
                'class' =>$password_class,
                'required' => $password_astrick,
				
                'disabled' => $isElementDisabled
            ]
        );

        if(!$model->getId()){

            $fieldset->addField(
                'confirm_password',
                'password',
                [
                    'name' => 'confirm_password',
                    'label' => __('Confirm Password'),
                    'title' => __('Confirm Password'),
                    'class' => $password_class,
                    'required' => $password_astrick,
                    
                    'disabled' => $isElementDisabled
                ]
            );
        }
					
       				
						

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
        return __('User Credential');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('User Credential');
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
