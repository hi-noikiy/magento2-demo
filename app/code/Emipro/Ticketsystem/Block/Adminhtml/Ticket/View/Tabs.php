<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Ticket\View;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs {

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Json\EncoderInterface $jsonEncoder, \Magento\Backend\Model\Auth\Session $authSession, \Magento\Framework\Registry $registry, array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    protected function _construct() {

        $this->setId('ticket_view_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Help Desk System'));
        parent::_construct();
    }

    protected function _beforeToHtml() {
        $this->addTab(
                'ticket_info', [
            'label' => __('Ticket Information'),
            'title' => __('Ticket Information'),
            'content' => $this->getLayout()->createBlock(
                    'Emipro\Ticketsystem\Block\Adminhtml\Ticket\View\Tab\Ticket'
            )->toHtml(),
            'active' => true
                ]
        );
        return parent::_beforeToHtml();
    }

}
