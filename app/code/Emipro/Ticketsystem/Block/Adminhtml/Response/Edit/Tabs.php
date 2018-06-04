<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Response\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs {

    protected function _construct() {
        parent::_construct();
        $this->setId('Response_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Frequent Response'));
    }

    protected function _beforeToHtml() {
        $this->addTab(
                'response_info', [
            'label' => __('Response'),
            'title' => __('Response'),
            'content' => $this->getLayout()->createBlock(
                    'Emipro\Ticketsystem\Block\Adminhtml\Response\Edit\Tab\Response'
            )->toHtml(),
            'active' => true
                ]
        );
        return parent::_beforeToHtml();
    }

}
