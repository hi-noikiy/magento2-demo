<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Response\Grid;
use Emipro\Ticketsystem\Helper\Data;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {

    protected $moduleManager;
    protected $_responseFactory;
    protected $_status;
      protected $_helper;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Emipro\Ticketsystem\Model\TicketresponseFactory $responseFactory, \Magento\Framework\Module\Manager $moduleManager, Data $helper,array $data = []
    ) {

        $this->_responseFactory = $responseFactory;
        $this->moduleManager = $moduleManager;
         $this->_helper = $helper;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct() {
        parent::_construct();
        $this->setId('gridGrid');
        $this->setDefaultSort('response_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = $this->_responseFactory->create()->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        $this->addColumn(
                'response_id', [
            'header' => __('ID'),
            'type' => 'number',
            'index' => 'response_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
                ]
        );
        $this->addColumn(
                'response_title', [
            'header' => __('Response Title'),
            'index' => 'response_title',
            'class' => 'xxx'
                ]
        );
        $this->addColumn(
                'status', [
            'header' => __('Status'),
             'type' => 'options',
            'index' => 'status',
              'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
                ]
        );
        $this->addColumn(
                'edit', [
            'header' => __('Edit'),
            'type' => 'action',
            'getter' => 'getId',
            'actions' => [
                [
                    'caption' => __('Edit'),
                    'url' => [
                        'base' => '*/*/edit'
                    ],
                    'field' => 'response_id'
                ]
            ],
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'header_css_class' => 'col-action',
            'column_css_class' => 'col-action'
                ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('response_id');
        $this->getMassactionBlock()->setFormFieldName('response_id');

        $this->getMassactionBlock()->addItem(
                'delete', [
            'label' => __('Delete'),
            'url' => $this->getUrl('emipro_ticketsystem/*/massDelete'),
            'confirm' => __('Are you sure?')
                ]
        );



        return $this;
    }

    public function getGridUrl() {
        return $this->getUrl('emipro_ticketsystem/response/grid');
     
    }

    public function getRowUrl($row) {
        return $this->getUrl('emipro_ticketsystem/*/edit', ['response_id' => $row->getId()]);
    }

}
