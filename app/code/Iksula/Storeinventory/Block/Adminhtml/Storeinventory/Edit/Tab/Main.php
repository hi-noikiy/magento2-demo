<?php

namespace Iksula\Storeinventory\Block\Adminhtml\Storeinventory\Edit\Tab;

/**
 * Storeinventory edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Iksula\Storeinventory\Model\Status
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
        \Iksula\Storeinventory\Model\Status $status,
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
        /* @var $model \Iksula\Storeinventory\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('storeinventory');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }


        $fieldset->addField(
            'sku',
            'text',
            [
                'name' => 'sku',
                'label' => __('Sku'),
                'title' => __('Sku'),

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'store_id',
            'text',
            [
                'name' => 'store_id',
                'label' => __('Store Code'),
                'title' => __('Store Code'),

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'original_price',
            'text',
            [
                'name' => 'original_price',
                'label' => __('Original Price'),
                'title' => __('Original Price'),

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'ecomm_price',
            'text',
            [
                'name' => 'ecomm_price',
                'label' => __('Ecomm Price'),
                'title' => __('Ecomm Price'),

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'buffer_inventory',
            'text',
            [
                'name' => 'buffer_inventory',
                'label' => __('Buffer Inventory'),
                'title' => __('Buffer Inventory'),

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'inventory',
            'text',
            [
                'name' => 'inventory',
                'label' => __('Inventory'),
                'title' => __('Inventory'),

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
