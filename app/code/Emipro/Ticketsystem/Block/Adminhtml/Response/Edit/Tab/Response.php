<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Response\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Emipro\Ticketsystem\Helper\Data;

class Response extends Generic {

    protected $_coreRegistry;
    protected $_helper;

    public function __construct(
    Context $context, Registry $registry, FormFactory $formFactory, Data $helper, array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm() {
        $data = $this->_coreRegistry->registry("response");
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('response_');
        $form->setFieldNameSuffix('response');
        $fieldset = $form->addFieldset(
                'base_fieldset', ['legend' => __('General')]
        );
        if (isset($data["response_id"]) && !empty($data["response_id"])) {
            $fieldset->addField(
                    'response_id', 'hidden', ['name' => 'response_id']
            );
        }

        $fieldset->addField(
                'response_title', 'text', [
            'name' => 'response_title',
            'label' => __('Response Title'),
            'required' => true
                ]
        );
        
        $fieldset->addField(
                'response_text', 'textarea', [
            'name' => 'response_text',
            'label' => __('Response Text'),
            'required' => true
                ]
        );

        $fieldset->addField(
                'status', 'select', [
            'name' => 'status',
            'label' => __('Status'),
            'required' => true,
            'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
                ]
        );
        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getTabLabel() {
        return __('Author');
    }

    public function getTabTitle() {
        return $this->getTabLabel();
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

}
