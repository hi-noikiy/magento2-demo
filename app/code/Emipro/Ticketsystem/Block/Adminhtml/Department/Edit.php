<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Department;

use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends FormContainer {

    protected function _construct() {
        $this->_objectId = 'department_id';
        $this->_blockGroup = 'emipro_ticketsystem';
        $this->_controller = 'adminhtml_department';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Department'));
        $this->buttonList->add(
                'save-and-continue', [
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => [
                        'event' => 'saveAndContinueEdit',
                        'target' => '#edit_form'
                    ]
                ]
            ]
                ], -100
        );
        $this->buttonList->update('delete', 'label', __('Delete Department'));
    }

}
