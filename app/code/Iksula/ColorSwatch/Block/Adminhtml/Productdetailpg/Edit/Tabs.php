<?php
namespace Iksula\ColorSwatch\Block\Adminhtml\Productdetailpg\Edit;

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
        $this->setId('productdetailpg_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Information'));
    }
}