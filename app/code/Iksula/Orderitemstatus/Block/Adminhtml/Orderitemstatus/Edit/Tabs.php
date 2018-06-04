<?php
namespace Iksula\Orderitemstatus\Block\Adminhtml\Orderitemstatus\Edit;

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
        $this->setId('orderitemstatus_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Orderitemstatus Information'));
    }
}