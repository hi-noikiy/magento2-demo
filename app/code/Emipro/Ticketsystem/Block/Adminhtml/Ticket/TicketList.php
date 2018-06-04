<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Ticket;

use Magento\Backend\Block\Widget\Container;

class TicketList extends Container {

    protected $_template = 'grid/view.phtml';

    public function __construct(
    \Magento\Backend\Block\Widget\Context $context, array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _prepareLayout() {

        $addButtonProps = [
            'id' => 'add_new_grid',
            'label' => __('Create New Ticket'),
            'class' => 'add primary',
            'button_class' => '',
             'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];
        $showClosed=$this->getRequest()->getParam("closed",0);
		 $lable="Show closed ticket";
		 if($showClosed){
			 		$lable="Hide closed ticket";
			}
        $this->buttonList->add('add_new', $addButtonProps);
 $closedButtonProps = [
            'id' => 'show_closed_ticket',
            'label' => __($lable),
            'class' => 'add primary ',
            'button_class' => 'action- scalable primary',
            'onclick' => "setLocation('" . $this->_getClosedUrl($showClosed) . "')"
        ];
        $this->buttonList->add('show_closed', $closedButtonProps);
        $this->setChild(
                'grid', $this->getLayout()->createBlock('Emipro\Ticketsystem\Block\Adminhtml\Ticket\Grid\Grid', 'grid.view.grid')
        );
        return parent::_prepareLayout();
    }

    

    protected function _getCreateUrl() {
        return $this->getUrl(
                        'emipro_ticketsystem/*/new'
        );
    }
    protected function _getClosedUrl($showClosed) {
		if($showClosed)
		{
			return $this->getUrl('emipro_ticketsystem/*/index');
		}
		return $this->getUrl('emipro_ticketsystem/*/index',['closed'=>1]);
		
    }

    public function getGridHtml() {
        return $this->getChildHtml('grid');
    }

}
