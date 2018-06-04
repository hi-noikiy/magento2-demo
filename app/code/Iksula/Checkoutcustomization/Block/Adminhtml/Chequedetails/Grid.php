<?php
namespace Iksula\Checkoutcustomization\Block\Adminhtml\Chequedetails;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Iksula\Checkoutcustomization\Model\chequedetailsFactory
     */
    protected $_chequedetailsFactory;

    /**
     * @var \Iksula\Checkoutcustomization\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Iksula\Checkoutcustomization\Model\chequedetailsFactory $chequedetailsFactory
     * @param \Iksula\Checkoutcustomization\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Iksula\Checkoutcustomization\Model\ChequedetailsFactory $ChequedetailsFactory,
        \Iksula\Checkoutcustomization\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_chequedetailsFactory = $ChequedetailsFactory;
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
        $collection = $this->_chequedetailsFactory->create()->getCollection();
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
					'order_id',
					[
						'header' => __('Order id '),
						'index' => 'order_id',
					]
				);

				$this->addColumn(
					'bank_name',
					[
						'header' => __('Bank Name'),
						'index' => 'bank_name',
					]
				);

				$this->addColumn(
					'cheque_no',
					[
						'header' => __('Cheque No'),
						'index' => 'cheque_no',
					]
				);

				$this->addColumn(
					'cheque_amount',
					[
						'header' => __('Cheque Amount'),
						'index' => 'cheque_amount',
					]
				);

				$this->addColumn(
					'date_of_cheque',
					[
						'header' => __('Date of Cheque'),
						'index' => 'date_of_cheque',
						'type'      => 'date',
					]
				);


				// $this->addColumn(
				// 	'created_at',
				// 	[
				// 		'header' => __('Created At'),
				// 		'index' => 'created_at',
				// 		'type'      => 'datetime',
				// 	]
				// );
				//
				//
				// $this->addColumn(
				// 	'updated_at',
				// 	[
				// 		'header' => __('Updated At'),
				// 		'index' => 'updated_at',
				// 		'type'      => 'datetime',
				// 	]
				// );





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



		   $this->addExportType($this->getUrl('checkoutcustomization/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('checkoutcustomization/*/exportExcel', ['_current' => true]),__('Excel XML'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }



    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('checkoutcustomization/*/index', ['_current' => true]);
    }

    /**
     * @param \Iksula\Checkoutcustomization\Model\chequedetails|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {

        return $this->getUrl(
            'checkoutcustomization/*/edit',
            ['id' => $row->getId()]
        );

    }



}
