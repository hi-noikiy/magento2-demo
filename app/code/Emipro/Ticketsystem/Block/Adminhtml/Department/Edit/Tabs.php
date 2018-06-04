<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Department\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs {

    protected function _construct() {
        parent::_construct();
        $this->setId('department_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Department Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab(
                'department_info', [
            'label' => __('Department'),
            'title' => __('Department'),
            'content' => $this->getLayout()->createBlock(
                    'Emipro\Ticketsystem\Block\Adminhtml\Department\Edit\Tab\Department'
            )->toHtml(),
            'active' => true
                ]
        );
        return parent::_beforeToHtml();
    }

}
