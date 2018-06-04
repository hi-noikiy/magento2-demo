<?php
namespace Iksula\Orderreturns\Block\Adminhtml\Orderreturn;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Iksula\Orderreturns\Model\orderreturnFactory
     */
    protected $_orderreturnFactory;

    /**
     * @var \Iksula\Orderreturns\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Iksula\Orderreturns\Model\orderreturnFactory $orderreturnFactory
     * @param \Iksula\Orderreturns\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Iksula\Orderreturns\Model\OrderreturnFactory $OrderreturnFactory,
        \Iksula\Orderreturns\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_orderreturnFactory = $OrderreturnFactory;
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
        $collection = $this->_orderreturnFactory->create()->getCollection();
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
						'header' => __('Order Id'),
						'index' => 'order_id',
					]
				);
								
				
				$this->addColumn(
					'quantity',
					[
						'header' => __('Quantity'),
						'index' => 'quantity',
					]
				);
				
				$this->addColumn(
					'return_reason',
					[
						'header' => __('Return Reason'),
						'index' => 'return_reason',
					]
				);
				
				$this->addColumn(
                    'product_sku',
                    [
                        'header' => __('Product Sku'),
                        'index' => 'product_sku',
                    ]
                );

                $this->addColumn(
                    'product_price',
                    [
                        'header' => __('Product Price '),
                        'index' => 'product_price',
                    ]
                );

                $this->addColumn(
					'return_price',
					[
						'header' => __('Return Price '),
						'index' => 'return_price',
					]
				);
				
				$this->addColumn(
					'product_id',
					[
						'header' => __('Product Id'),
						'index' => 'product_id',
					]
				);
				
						
						$this->addColumn(
							'return_status',
							[
								'header' => __(' Return Status'),
								'index' => 'return_status',
								'type' => 'options',
								'options' => \Iksula\Orderreturns\Block\Adminhtml\Orderreturn\Grid::getOptionArray12()
							]
						);
						
						
				$this->addColumn(
					'customer_id',
					[
						'header' => __('Customer Id'),
						'index' => 'customer_id',
					]
				);
				
				$this->addColumn(
					'pickup_time',
					[
						'header' => __('Pickup Time'),
						'index' => 'pickup_time',
					]
				);
				
				$this->addColumn(
					'pickup_date',
					[
						'header' => __('Pickup Date'),
						'index' => 'pickup_date',
						'type'      => 'date',
					]
				);
					
					
				// $this->addColumn(
				// 	'created_at',
				// 	[
				// 		'header' => __('Created At'),
				// 		'index' => 'created_at',
				// 	]
				// );
				
				// $this->addColumn(
				// 	'updated_at',
				// 	[
				// 		'header' => __('Updated At'),
				// 		'index' => 'updated_at',
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
        //$this->getMassactionBlock()->setTemplate('Iksula_Orderreturns::orderreturn/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('orderreturn');

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
     * @param \Iksula\Orderreturns\Model\orderreturn|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'orderreturns/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	
		static public function getOptionArray12()
		{
            $data_array=array(); 
            $data_array[0]='Return Pending';
            $data_array[1]='Return Pickup Schedule';
            $data_array[2]='Return Received';
            $data_array[3]='Refund via Store Credit';
            $data_array[4]='Refund via Card';
            return($data_array);
		}
		
        static public function getValueArray12()
		{
            $data_array=array();
			foreach(\Iksula\Orderreturns\Block\Adminhtml\Orderreturn\Grid::getOptionArray12() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}

        static public function getReturnStatus($statusdata){        
            
             if($statusdata==0){
                $data_array=array();
                $data_array[0]='Return Pending'; 
                $data_array[1]='Return Pickup Schedule';
                $data_array[2]='Return Received';
                $data_array[3]='Refund via Store Credit';
                $data_array[4]='Refund via Card';
            }elseif($statusdata==1){

                $data_array=array(); 
                $data_array[1]='Return Pickup Schedule';
                $data_array[2]='Return Received';
                $data_array[3]='Refund via Store Credit';
                $data_array[4]='Refund via Card';
            }elseif($statusdata==2){

                $data_array=array(); 
                $data_array[2]='Return Received';
                $data_array[3]='Refund via Store Credit';
                $data_array[4]='Refund via Card';
            }elseif($statusdata==3){

                $data_array=array(); 
                $data_array[3]='Refund via Store Credit';
            }else{        
                $data_array[4]='Refund via Card';
            }

        return($data_array);

    }
		

}