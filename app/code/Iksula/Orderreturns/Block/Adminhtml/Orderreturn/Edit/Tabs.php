<?php
namespace Iksula\Orderreturns\Block\Adminhtml\Orderreturn\Edit;

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
        $this->setId('orderreturn_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Orderreturn Information'));
    }
}