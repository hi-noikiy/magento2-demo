<?php
namespace Iksula\Checkoutcustomization\Block\Adminhtml\Chequedetails\Edit;

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
        $this->setId('chequedetails_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Chequedetails Information'));
    }
}