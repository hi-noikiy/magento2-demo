<?php
namespace Iksula\NavisionApi\Block\Adminhtml\Navisionapi\Edit;

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
        $this->setId('navisionapi_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Navisionapi Information'));
    }
}