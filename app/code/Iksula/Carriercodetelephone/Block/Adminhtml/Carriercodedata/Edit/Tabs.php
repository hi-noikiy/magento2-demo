<?php
namespace Iksula\Carriercodetelephone\Block\Adminhtml\Carriercodedata\Edit;

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
        $this->setId('carriercodedata_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Carriercodedata Information'));
    }
}