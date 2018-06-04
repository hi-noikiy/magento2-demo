<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Ticket\Grid;

use Magento\Backend\Block\Widget\Grid\Extended;

class Grid extends Extended {

    protected $_ticketSystemFactory;
    protected $_helper;
    protected $_resource;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context,\Magento\Framework\App\ResourceConnection $resource,
     \Magento\Backend\Helper\Data $backendHelper, \Emipro\Ticketsystem\Model\TicketSystemFactory $ticketSystemFactory,
      \Magento\Framework\Module\Manager $moduleManager, \Emipro\Ticketsystem\Helper\Data $helper, array $data = []
    ) {
        $this->_helper = $helper;
        $this->_ticketSystemFactory = $ticketSystemFactory;
        $this->moduleManager = $moduleManager;
        $this->_resource = $resource;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct() {
        parent::_construct();
        $this->setId('gridGrid');
        $this->setDefaultSort('date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection() {
		$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
		$connection  = $this->_resource->getConnection();
		$tableConv   = $connection->getTableName('emipro_ticket_conversation');
		
		$showClosed=$this->getRequest()->getParam("closed",0);
        $superAdmin = $object_manager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('emipro/emipro_group/ticket_admin'); 
        $super_admin_id = explode(",", $superAdmin);
        $current_AdminId = $object_manager->get('\Magento\Backend\Model\Auth\Session')->getUser()->getUserId();
        $collection = $this->_ticketSystemFactory->create()->getCollection();
		if($showClosed)
		{
			if(!in_array($current_AdminId,$super_admin_id))
			{
			$collection->getSelect()
			   ->joinLeft(array('conv' => $tableConv),'main_table.ticket_id = conv.ticket_id',array('conv.discussion_admin'))
			   ->where("conv.discussion_admin = ".$current_AdminId ." OR main_table.assign_admin_id = ".$current_AdminId)
			   ->group('main_table.ticket_id');
		   }
		}
		else
		{
			if(!in_array($current_AdminId,$super_admin_id))
			{ 
			$collection->getSelect()
			   ->joinLeft(array('conv' => $tableConv),'main_table.ticket_id = conv.ticket_id',array('conv.discussion_admin'))
			   ->where("(conv.discussion_admin = ".$current_AdminId ." OR main_table.assign_admin_id = ".$current_AdminId.") AND main_table.status_id!=2")
			   ->group('main_table.ticket_id');
		   }
           else
           {
			$collection = $this->_ticketSystemFactory->create()->getCollection();
			$collection->addFieldToFilter("status_id",array("neq"=>2));
		   }
		}
		
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns() {
        $this->addColumn(
            'ticket_id', [
            'header' => __('ID'),
            'type' => 'number',
            'index' => 'ticket_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
            'filter_index' => 'main_table.ticket_id'
                ]
        );
        $this->addColumn(
                'orderid', [
            'header' => __('Order #'),
            'index' => 'orderid',
            'type' => 'text',
                ]
        );
        $this->addColumn(
                'customer_id', [
            'header' => __('Customer Name'),
            'index' => 'customer_id',
            'renderer' => 'Emipro\Ticketsystem\Block\Adminhtml\Ticket\Renderer\CustomerName',
            'filter_condition_callback' => array($this, '_customerNameFilter')
                ]
        );

        $this->addColumn(
                'subject', [
            'header' => __('Subject'),
            'index' => 'subject',
            'type' => 'text',
                ]
        );
        $this->addColumn(
                'status_id', [
            'header' => __('Status'),
            'index' => 'status_id',
            'type' => 'options',
            'options' => $this->_helper->getTicketstatus(),
            'filter_index' => 'main_table.status_id'
                ]
        );
        $this->addColumn(
                'priority_id', [
            'header' => __('Priority'),
            'index' => 'priority_id',
            'type' => 'options',
            'options' => $this->_helper->getTicketpriority()
                ]
        );
        $this->addColumn(
                'assign_admin_id', [
            'header' => __('Assignee/Support Person'),
            'index' => 'assign_admin_id',
            'type' => 'options',
            'options' => $this->_helper->getAdminUser()
                ]
        );
        $this->addColumn(
                'date', [
            'header' => __('Last Updated Date'),
            'index' => 'date',
            'type' => 'datetime',
            'header_css_class' => 'col-date',
            'column_css_class' => 'col-date',
             'filter_index' => 'main_table.date'

                ]
        );
        $this->addColumn(
                'edit', [
            'header' => __('Edit'),
            'type' => 'action',
            'getter' => 'getId',
            'actions' => [
                [
                    'caption' => __('View'),
                    'url' => [
                        'base' => '*/*/view'
                    ],
                    'field' => 'ticket_id'
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

    /**
     * @return $this
     */
    protected function _prepareMassaction() {
        $this->setMassactionIdField('ticket_id');
        $this->getMassactionBlock()->setFormFieldName('ticket_id');

        $this->getMassactionBlock()->addItem(
                'delete', [
            'label' => __('Delete'),
            'url' => $this->getUrl('emipro_ticketsystem/*/massDelete'),
            'confirm' => __('Are you sure?')
                ]
        );



        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl() {
        return $this->getUrl('emipro_ticketsystem/*/grid', ['_current' => true]);
    }

    public function getRowUrl($row) {
        return $this->getUrl(
                        'emipro_ticketsystem/*/view', ['ticket_id' => $row->getId()]
        );
    }

    protected function _customerNameFilter($collection, $column) {


        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $collection->getSelect()
                ->joinLeft(array('customer' => "customer_entity"), "customer.entity_id=main_table.customer_id")
                ->joinLeft(array('firstname' => "customer_entity_varchar"), "firstname.entity_id=main_table.customer_id")
                ->joinLeft(array('lastname' => "customer_entity_varchar"), "lastname.entity_id=main_table.customer_id")
                ->where("main_table.customer_name like ? OR firstname.value like ? OR lastname.value like ?", "%$value%")->group("main_table.ticket_id");

        return $collection;
    }

}
