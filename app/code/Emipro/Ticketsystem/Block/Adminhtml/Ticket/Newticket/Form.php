<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Ticket\Newticket;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;
use Emipro\Ticketsystem\Helper\Data;

class Form extends Generic {

    protected $_coreRegistry;
    protected $helper;

    public function __construct(
    Context $context, Registry $registry, FormFactory $formFactory, Config $wysiwygConfig, Data $helper, array $data = []
    ) {

        $this->_coreRegistry = $registry;
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function _prepareForm() {
        $customerId = $this->getRequest()->getParam('id');

        $form = $this->_formFactory->create(
                [
                    'data' => [
                        'id' => 'edit_form',
                        'action' => $this->getUrl('emipro_ticketsystem/*/newticket', ['_secure' => true]),
                        'method' => 'post',
                        'enctype' => 'multipart/form-data'
                    ]
                ]
        );
        $form->setHtmlIdPrefix('ticketdata_');
        $fieldset = $form->addFieldset(
                'base_fieldset', ['legend' => __('New Support Ticket')]
        );

        $fieldset->addField(
                'customer_id', 'hidden', [
            'name' => 'customer_id',
            'label' => __('customer_id'),
            'value' => $customerId
                ]
        );

        $fieldset->addField('customer_name', 'label', [
            'label' => __('Customer Name'),
            'name' => 'customer_name',
            'value' => $this->helper->getCustomerName($customerId),
        ]);

        $fieldset->addField('department_id', 'select', [
            'label' => __('Department'),
            'required' => true,
            'name' => 'department_id',
            'values' => ["" => __("Please Select Department")] + $this->helper->getTicketdept(),
        ]);

        $fieldset->addField('priority_id', 'select', [
            'label' => __('Priority'),
            'required' => true,
            'name' => 'priority_id',
            'values' => ["" => __("Please Select Priority")] + $this->helper->getTicketpriority(),
        ]);


        $fieldset->addField('orderid', 'select', [
            'label' => __('Order Id'),
            'name' => "orderid",
            'values' => ["" => __("Please Select Order Id")] + $this->helper->getCustomerOrderIds($customerId),
        ]);

        $fieldset->addField('subject', 'text', [
            'label' => __('Subject'),
            'name' => "subject",
            'required' => true,
        ]);

        $fieldset->addField('message', 'textarea', [
            'label' => __('Message'),
            'name' => "message",
            'required' => true,
        ]);

        $fieldset->addField('file', 'file', [
            'label' => __('Attachment'),
            'name' => "file",
        ]);


        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
