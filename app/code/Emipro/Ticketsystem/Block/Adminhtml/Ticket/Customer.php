<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Ticket;

use Magento\Backend\Block\Widget\Container;

class Customer extends Container {

    protected $_template = 'grid/view.phtml';

    public function __construct(
    \Magento\Backend\Block\Widget\Context $context, array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _prepareLayout() {
        $this->setChild(
                'grid', $this->getLayout()->createBlock('Emipro\Ticketsystem\Block\Adminhtml\Ticket\Customer\Grid', 'customer.view.grid')
        );
        return parent::_prepareLayout();
    }

    public function getGridHtml() {
        return $this->getChildHtml('grid');
    }

}
