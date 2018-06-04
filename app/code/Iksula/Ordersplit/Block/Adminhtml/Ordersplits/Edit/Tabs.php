<?php
namespace Iksula\Ordersplit\Block\Adminhtml\Ordersplits\Edit;

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
        $this->setId('ordersplits_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Ordersplits Information'));
    }
}