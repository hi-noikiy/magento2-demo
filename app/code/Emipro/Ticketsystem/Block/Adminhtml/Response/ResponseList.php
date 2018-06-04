<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Response;

use Magento\Backend\Block\Widget\Container;

class ResponseList extends Container {

    protected $_template = 'grid/view.phtml';

    public function __construct(
    \Magento\Backend\Block\Widget\Context $context, array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _prepareLayout() {

         $addButtonProps = [
            'id' => 'responseGrid',
            'label' => __('Add New Response'),
            'class' => 'add',
            'button_class' => '',
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')",
            'style' => "background-color: #eb5202;border-color: #eb5202;color: #fff;"
        ];
        $this->buttonList->add('add_new', $addButtonProps);   
        $this->setChild(
                'grid', $this->getLayout()->createBlock('Emipro\Ticketsystem\Block\Adminhtml\Response\Grid\Grid', 'grid.view.grid')
        );
        return parent::_prepareLayout();
    }

    protected function _getCreateUrl() {
        return $this->getUrl(
                        'emipro_ticketsystem/*/new'
        );
    }

    public function getGridHtml() {
        return $this->getChildHtml('grid');
    }

}
