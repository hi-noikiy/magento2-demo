<?php
namespace Iksula\NavisionApi\Block\Adminhtml\Navisionapi;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Iksula\NavisionApi\Model\navisionapiFactory
     */
    protected $_navisionapiFactory;

    /**
     * @var \Iksula\NavisionApi\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Iksula\NavisionApi\Model\navisionapiFactory $navisionapiFactory
     * @param \Iksula\NavisionApi\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Iksula\NavisionApi\Model\NavisionapiFactory $NavisionapiFactory,
        \Iksula\NavisionApi\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_navisionapiFactory = $NavisionapiFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('api_increment_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_navisionapiFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'api_increment_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'api_increment_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );


		
				$this->addColumn(
					'method_name',
					[
						'header' => __('Method Name'),
						'index' => 'method_name',
					]
				);
				
				$this->addColumn(
					'request',
					[
						'header' => __('Request'),
						'index' => 'request',
					]
				);
				
				$this->addColumn(
					'response',
					[
						'header' => __('Response'),
						'index' => 'response',
					]
				);
				
				$this->addColumn(
					'request_datetime',
					[
						'header' => __('Request Datetime'),
						'index' => 'request_datetime',
					]
				);
				
				$this->addColumn(
					'response_datetime',
					[
						'header' => __('Response Datetime'),
						'index' => 'response_datetime',
					]
				);
				


		
        //$this->addColumn(
            //'edit',
            //[
                //'header' => __('Edit'),
                //'type' => 'action',
                //'getter' => 'getId',
                //'actions' => [
                    //[
                        //'caption' => __('Edit'),
                        //'url' => [
                            //'base' => '*/*/edit'
                        //],
                        //'field' => 'api_increment_id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);
		

		
		   $this->addExportType($this->getUrl('navisionapi/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('navisionapi/*/exportExcel', ['_current' => true]),__('Excel XML'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

	
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('api_increment_id');
        //$this->getMassactionBlock()->setTemplate('Iksula_NavisionApi::navisionapi/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('navisionapi');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('navisionapi/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('navisionapi/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );


        return $this;
    }
		

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('navisionapi/*/index', ['_current' => true]);
    }

    /**
     * @param \Iksula\NavisionApi\Model\navisionapi|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'navisionapi/*/edit',
            ['api_increment_id' => $row->getId()]
        );
		
    }

	

}