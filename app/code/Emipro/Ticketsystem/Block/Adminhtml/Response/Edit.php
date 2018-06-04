<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Response;

use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends FormContainer {

    protected function _construct() {
        $this->_objectId = 'response_id';
        $this->_blockGroup = 'emipro_ticketsystem';
        $this->_controller = 'adminhtml_response';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Response'));
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
        $this->buttonList->update('delete', 'label', __('Delete Response'));
    }

}
