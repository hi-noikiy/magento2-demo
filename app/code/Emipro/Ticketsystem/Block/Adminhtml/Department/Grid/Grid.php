<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Department\Grid;
use Emipro\Ticketsystem\Helper\Data;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {

    protected $moduleManager;
    protected $_departmentFactory;
    protected $_status;
      protected $_helper;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Emipro\Ticketsystem\Model\TicketdepartmentFactory $departmentFactory, \Magento\Framework\Module\Manager $moduleManager, Data $helper,array $data = []
    ) {

        $this->_departmentFactory = $departmentFactory;
        $this->moduleManager = $moduleManager;
         $this->_helper = $helper;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct() {
        parent::_construct();
        $this->setId('gridGrid');
        $this->setDefaultSort('department_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = $this->_departmentFactory->create()->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        $this->addColumn(
                'department_id', [
            'header' => __('ID'),
            'type' => 'number',
            'index' => 'department_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
                ]
        );
        $this->addColumn(
                'department_name', [
            'header' => __('Department Name'),
            'index' => 'department_name',
            'class' => 'xxx'
                ]
        );

        $this->addColumn(
                'admin_user_id', [
            'header' => __('Default Assignee'),
            'index' => 'admin_user_id',
             'type' => 'options',     
              'options' => $this->_helper->getAdminUser()
                ]
        );
        $this->addColumn(
                'status', [
            'header' => __('Status'),
             'type' => 'options',
            'index' => 'status',
              'options' => ['1' => __('Active'), '0' => __('Inactive')]
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
                    'field' => 'department_id'
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
        $this->setMassactionIdField('department_id');
        $this->getMassactionBlock()->setFormFieldName('department_id');

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
        return $this->getUrl('emipro_ticketsystem/*/grid', ['_current' => true]);
    }

    public function getRowUrl($row) {
        return $this->getUrl(
                        'emipro_ticketsystem/*/edit', ['department_id' => $row->getId()]
        );
    }

}
