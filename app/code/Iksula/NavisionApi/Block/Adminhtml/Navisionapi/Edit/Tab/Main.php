<?php

namespace Iksula\NavisionApi\Block\Adminhtml\Navisionapi\Edit\Tab;

/**
 * Navisionapi edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Iksula\NavisionApi\Model\Status
     */
    protected $_status;

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
        \Iksula\NavisionApi\Model\Status $status,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
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
        /* @var $model \Iksula\NavisionApi\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('navisionapi');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('api_increment_id', 'hidden', ['name' => 'api_increment_id']);
        }

		
        $fieldset->addField(
            'method_name',
            'text',
            [
                'name' => 'method_name',
                'label' => __('Method Name'),
                'title' => __('Method Name'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'request',
            'text',
            [
                'name' => 'request',
                'label' => __('Request'),
                'title' => __('Request'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'response',
            'text',
            [
                'name' => 'response',
                'label' => __('Response'),
                'title' => __('Response'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'request_datetime',
            'text',
            [
                'name' => 'request_datetime',
                'label' => __('Request Datetime'),
                'title' => __('Request Datetime'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'response_datetime',
            'text',
            [
                'name' => 'response_datetime',
                'label' => __('Response Datetime'),
                'title' => __('Response Datetime'),
				
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
