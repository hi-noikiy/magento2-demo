<?php
namespace Iksula\Orderreturns\Block\Adminhtml\Returnreason;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Iksula\Orderreturns\Model\returnreasonFactory
     */
    protected $_returnreasonFactory;

    /**
     * @var \Iksula\Orderreturns\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Iksula\Orderreturns\Model\returnreasonFactory $returnreasonFactory
     * @param \Iksula\Orderreturns\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Iksula\Orderreturns\Model\ReturnreasonFactory $ReturnreasonFactory,
        \Iksula\Orderreturns\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_returnreasonFactory = $ReturnreasonFactory;
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
        $collection = $this->_returnreasonFactory->create()->getCollection();
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
                'return_reason',
                [
                    'header' => __('Return Reason'),
                    'type' => 'text',
                    'index' => 'return_reason',
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id'
                ]
                );
						
				$this->addColumn(
					'status',
					[
						'header' => __('Status'),
						'index' => 'status',
						'type' => 'options',
						'options' => \Iksula\Orderreturns\Block\Adminhtml\Returnreason\Grid::getOptionArray2()
					]
				);
						
						
				$this->addColumn(
					'created_at',
					[
						'header' => __('Created_at'),
						'index' => 'created_at',
						'type'      => 'datetime',
					]
				);
					
					
				$this->addColumn(
					'updated_at',
					[
						'header' => __('Updated_at'),
						'index' => 'updated_at',
						'type'      => 'datetime',
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
		

		
		   $this->addExportType($this->getUrl('orderreturns/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('orderreturns/*/exportExcel', ['_current' => true]),__('Excel XML'));

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
        //$this->getMassactionBlock()->setTemplate('Iksula_Orderreturns::returnreason/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('returnreason');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('orderreturns/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('orderreturns/*/massStatus', ['_current' => true]),
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
        return $this->getUrl('orderreturns/*/index', ['_current' => true]);
    }

    /**
     * @param \Iksula\Orderreturns\Model\returnreason|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'orderreturns/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	
		static public function getOptionArray2()
		{
            $data_array=array(); 
			$data_array[0]='Enable';
			$data_array[1]='Disable';
            return($data_array);
		}
		static public function getValueArray2()
		{
            $data_array=array();
			foreach(\Iksula\Orderreturns\Block\Adminhtml\Returnreason\Grid::getOptionArray2() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		

}