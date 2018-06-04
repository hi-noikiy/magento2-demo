<?php
namespace Iksula\Orderreturns\Block\Adminhtml\Returnreason\Edit;

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
        $this->setId('returnreason_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Returnreason Information'));
    }
}