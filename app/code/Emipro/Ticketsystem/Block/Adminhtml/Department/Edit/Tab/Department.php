<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Department\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Emipro\Ticketsystem\Helper\Data;

class Department extends Generic {

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
        $data = $this->_coreRegistry->registry("department");
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('department_');
        $form->setFieldNameSuffix('department');
        $fieldset = $form->addFieldset(
                'base_fieldset', ['legend' => __('General')]
        );
        if (isset($data["department_id"]) && !empty($data["department_id"])) {
            $fieldset->addField(
                    'department_id', 'hidden', ['name' => 'department_id']
            );
        }

        $fieldset->addField(
                'department_name', 'text', [
            'name' => 'department_name',
            'label' => __('Department Name'),
            'required' => true
                ]
        );

        $fieldset->addField(
                'status', 'select', [
            'name' => 'status',
            'label' => __('Status'),
            'required' => true,
            'options' => ['1' => __('Active'), '0' => __('Inactive')]
                ]
        );
        $fieldset->addField(
                'admin_user_id', 'select', [
            'name' => 'admin_user_id',
            'label' => __('Default Assignee'),
            'required' => true,
            'options' => ["" => __('Select Assignee')] + $this->_helper->getAdminUser()
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
