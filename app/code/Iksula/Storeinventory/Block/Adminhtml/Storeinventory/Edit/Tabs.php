<?php
namespace Iksula\Storeinventory\Block\Adminhtml\Storeinventory\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('storeinventory_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Storeinventory Information'));
    }
}