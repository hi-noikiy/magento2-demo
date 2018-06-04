<?php
namespace Iksula\Storemanager\Block\Adminhtml\Storemanager;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Iksula\Storemanager\Model\storemanagerFactory
     */
    protected $_storemanagerFactory;

    /**
     * @var \Iksula\Storemanager\Model\Status
     */
    protected $_status;

    

    

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Iksula\Storemanager\Model\storemanagerFactory $storemanagerFactory
     * @param \Iksula\Storemanager\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Iksula\Storemanager\Model\StoremanagerFactory $StoremanagerFactory,
        \Iksula\Storemanager\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,                
        array $data = []
    ) {
        $this->_storemanagerFactory = $StoremanagerFactory;
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
        $this->setDefaultSort('storemanager_id');
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
        $collection = $this->_storemanagerFactory->create()->getCollection();
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
            'storemanager_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'storemanager_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );


		
				$this->addColumn(
					'store_name',
					[
						'header' => __('Store Name'),
						'index' => 'store_name',
					]
				);
				
				$this->addColumn(
					'store_code',
					[
						'header' => __('Store Code'),
						'index' => 'store_code',
					]
				);
				
				$this->addColumn(
					'store_username',
					[
						'header' => __('Store Username'),
						'index' => 'store_username',
					]
				);
				
				$this->addColumn(
					'store_country',
					[
						'header' => __('Store Country'),
						'index' => 'store_country',
					]
				);
				
				$this->addColumn(
					'store_state',
					[
						'header' => __('Store State'),
						'index' => 'store_state',
					]
				);
				
				$this->addColumn(
					'store_city',
					[
						'header' => __('Store City'),
						'index' => 'store_city',
					]
				);
				
				$this->addColumn(
					'store_pincode',
					[
						'header' => __('Store Pincode'),
						'index' => 'store_pincode',
					]
				);
				
				$this->addColumn(
					'store_longitude',
					[
						'header' => __('Store Longitude'),
						'index' => 'store_longitude',
					]
				);
				
				$this->addColumn(
					'store_latitude',
					[
						'header' => __('Store Latitude'),
						'index' => 'store_latitude',
					]
				);
				
				$this->addColumn(
					'store_type',
					[
						'header' => __('Store Type'),
						'index' => 'store_type',
					]
				);
				
				$this->addColumn(
					'store_address',
					[
						'header' => __('Store Address'),
						'index' => 'store_address',
					]
				);
				
				$this->addColumn(
					'store_mobileno',
					[
						'header' => __('Store Mobile Number'),
						'index' => 'store_mobileno',
					]
				);
				
				$this->addColumn(
					'store_emailid',
					[
						'header' => __('Store Email Id'),
						'index' => 'store_emailid',
					]
				);
				
						
						$this->addColumn(
							'store_status',
							[
								'header' => __('Store Status'),
								'index' => 'store_status',
								'type' => 'options',
								'options' => \Iksula\Storemanager\Block\Adminhtml\Storemanager\Grid::getOptionArray13()
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
                        //'field' => 'storemanager_id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);
		

		
		   $this->addExportType($this->getUrl('storemanager/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('storemanager/*/exportExcel', ['_current' => true]),__('Excel XML'));

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

        $this->setMassactionIdField('storemanager_id');
        //$this->getMassactionBlock()->setTemplate('Iksula_Storemanager::storemanager/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('storemanager');


        return $this;
    }
		

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('storemanager/*/index', ['_current' => true]);
    }

    /**
     * @param \Iksula\Storemanager\Model\storemanager|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'storemanager/*/edit',
            ['storemanager_id' => $row->getId()]
        );
		
    }

	
		static public function getOptionArray13()
		{
            $data_array=array(); 
			$data_array[1]='Yes';
			$data_array[0]='No';
            return($data_array);
		}
		static public function getValueArray13()
		{
            $data_array=array();
			foreach(\Iksula\Storemanager\Block\Adminhtml\Storemanager\Grid::getOptionArray13() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}

        static public function getOptionArrayStoreType()
        {
            $data_array=array(); 
            $data_array['store']='Store';
            $data_array['warehouse']='Warehouse';
            return($data_array);
        }

        static public function getValueArrayStoreType()
        {
            $data_array=array();
            foreach(\Iksula\Storemanager\Block\Adminhtml\Storemanager\Grid::getOptionArrayStoreType() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);        
            }
            return($data_array);

        }

        static public function getOptionArrayCountry()
        {
            $data_array=array();             

            return($data_array);
        }

        static public function getValueArrayCountry()
        {
            $data_array=array();
            foreach(\Iksula\Storemanager\Block\Adminhtml\Storemanager\Grid::getOptionArrayCountry() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);        
            }
            return($data_array);

        }
		

}