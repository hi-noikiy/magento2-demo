<?php
namespace Iksula\Storeinventory\Block\Adminhtml\Storeinventory;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Iksula\Storeinventory\Model\storeinventoryFactory
     */
    protected $_storeinventoryFactory;

    /**
     * @var \Iksula\Storeinventory\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Iksula\Storeinventory\Model\storeinventoryFactory $storeinventoryFactory
     * @param \Iksula\Storeinventory\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Iksula\Storeinventory\Model\StoreinventoryFactory $StoreinventoryFactory,
        \Iksula\Storeinventory\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_storeinventoryFactory = $StoreinventoryFactory;
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
        $this->setDefaultSort('id');
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
        $collection = $this->_storeinventoryFactory->create()->getCollection();
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
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );



				$this->addColumn(
					'sku',
					[
						'header' => __('Sku'),
						'index' => 'sku',
					]
				);

				$this->addColumn(
					'store_id',
					[
						'header' => __('Store Code'),
						'index' => 'store_id',
					]
				);

				$this->addColumn(
					'original_price',
					[
						'header' => __('Original Price'),
						'index' => 'original_price',
					]
				);

				$this->addColumn(
					'ecomm_price',
					[
						'header' => __('Ecomm Price'),
						'index' => 'ecomm_price',
					]
				);

				$this->addColumn(
					'buffer_inventory',
					[
						'header' => __('Buffer Inventory'),
						'index' => 'buffer_inventory',
					]
				);

				$this->addColumn(
					'inventory',
					[
						'header' => __('Inventory'),
						'index' => 'inventory',
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
                        //'field' => 'id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);



		   $this->addExportType($this->getUrl('storeinventory/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('storeinventory/*/exportExcel', ['_current' => true]),__('Excel XML'));

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

        $this->setMassactionIdField('id');
        //$this->getMassactionBlock()->setTemplate('Iksula_Storeinventory::storeinventory/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('storeinventory');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('storeinventory/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('storeinventory/*/massStatus', ['_current' => true]),
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
        return $this->getUrl('storeinventory/*/index', ['_current' => true]);
    }

    /**
     * @param \Iksula\Storeinventory\Model\storeinventory|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {

        return $this->getUrl(
            'storeinventory/*/edit',
            ['id' => $row->getId()]
        );

    }



}
