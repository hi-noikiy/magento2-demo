<?php
namespace Iksula\Ordersplit\Block\Adminhtml\Ordersplits;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Iksula\Ordersplit\Model\ordersplitsFactory
     */
    protected $_ordersplitsFactory;

    /**
     * @var \Iksula\Ordersplit\Model\Status
     */
    protected $_status;


    protected $_userFactory;

    private $roleFactory;

    protected $authSession;


    protected $storemanager;
    protected $_orderCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Iksula\Ordersplit\Model\ordersplitsFactory $ordersplitsFactory
     * @param \Iksula\Ordersplit\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Iksula\Ordersplit\Model\OrdersplitsFactory $OrdersplitsFactory,
        \Iksula\Ordersplit\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Iksula\Storemanager\Model\StoremanagerFactory $storemanager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        array $data = []
    ) {
        $this->_ordersplitsFactory = $OrdersplitsFactory;
        $this->_status = $status;

        $this->_userFactory = $userFactory;
        $this->roleFactory = $roleFactory;
        $this->moduleManager = $moduleManager;
        $this->authSession = $authSession;
        $this->storemanager = $storemanager;
        $this->_orderCollectionFactory = $orderCollectionFactory;
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


    protected function getCurrentUser(){


        return $this->authSession->getUser();

    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {

        $user_id = "";
        $user_id = $this->getCurrentUser()->getUserId();


        $collection = $this->_ordersplitsFactory->create()->getCollection();

        $Adminuser = $this->checkIfAdmin($user_id);



        if(!$Adminuser){

            $role_id = $this->_userFactory->create()->load($user_id)->getRole()->getRoleId();


            $store_ids = $this->storemanager->create()->getCollection()
            ->addFieldToSelect('storemanager_id')->addFieldToFilter('role_id_mapping' , array('eq' => $role_id))->getData();

            $storeIds = array_column($store_ids, 'storemanager_id');

            $iFirstStoreId = $storeIds[0];

            $collection->addFieldToFilter('allocated_storeids',
                    array(
                        array('finset'=> array($iFirstStoreId))
                    )
            );

        }

        //$collection->setOrder('order_id', 'DESC');
        $collection->getSelect()->order('order_id DESC');



        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }


    protected function checkIfAdmin($user_id){

        if(($user_id != "") && ($user_id != 1)){
            return false;
        }else{
            return true;
        }


    }



    public function filterCallbackOrderIds($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $orderCollection = $this->_orderCollectionFactory->create()                    
                                        ->addFieldToFilter('increment_id',array('like'=>'%'.$value.'%'))
                                        ->addFieldToSelect('entity_id');
        $collectiondata = $orderCollection->getData();
        $order_ids_array = array();
        foreach ($collectiondata as  $value) {
            array_push($order_ids_array, $value['entity_id']);
        }
        if($order_ids_array){            
            $order_ids = implode(',', $order_ids_array);
            $collection->addFieldToFilter('order_id', array('in' => $order_ids));
        }                

        return $collection;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        // $this->addColumn(
        //     'id',
        //     [
        //         'header' => __('ID'),
        //         'type' => 'number',
        //         'index' => 'id',
        //         'header_css_class' => 'col-id',
        //         'column_css_class' => 'col-id'
        //     ]
        // );



				$this->addColumn(
					'order_id',
					[
						'header' => __('Order Id'),
						'index' => 'order_id',
            'renderer' => 'Iksula\Ordersplit\Block\Adminhtml\Form\Renderer\OrderIncrementId',
            'filter_condition_callback' => array($this, 'filterCallbackOrderIds'),
					]
				);

				$this->addColumn(
					'order_item_id',
					[
						'header' => __('Order Item Id'),
						'index' => 'order_item_id',
					]
				);

				// $this->addColumn(
				// 	'picklist_id',
				// 	[
				// 		'header' => __('Pick List Id'),
				// 		'index' => 'picklist_id',
				// 	]
				// );

				// $this->addColumn(
				// 	'order_date',
				// 	[
				// 		'header' => __('Order Date'),
				// 		'index' => 'order_date',
				// 		'type'      => 'datetime',
				// 	]
				// );


        $this->addColumn(
					'order_items_data',
					[
						'header' => __('Order Items Data'),
						'index' => 'order_items_data',
            'renderer' => 'Iksula\Ordersplit\Block\Adminhtml\Form\Renderer\OrderItemsData',
            'filter' => false

					]
				);


				$this->addColumn(
					'order_item_status',
					[
						'header' => __('Order Item Status'),
						'index' => 'order_item_status',
            'renderer' => 'Iksula\Ordersplit\Block\Adminhtml\Form\Renderer\OrderItemsStatusLabel',
            'filter' => false
					]
				);

        $user_id = "";
        $user_id = $this->getCurrentUser()->getUserId();

        $Adminuser = $this->checkIfAdmin($user_id);


          if($Adminuser){

                $this->addColumn('manualallocation', ['header' => __('Manual allocation'),
                                'index' => 'manualallocation',
                                'renderer' => 'Iksula\Ordersplit\Block\Adminhtml\Form\Renderer\Manualallocation',
                                'filter' => false]
                                );
          }


          //if(!$Adminuser){


                $this->addColumn('accept-reject', ['header' => __('Accept/Reject'),
                                'index' => 'accept/reject',
                                'renderer' => 'Iksula\Ordersplit\Block\Adminhtml\Form\Renderer\Acceptreject',
                                'filter' => false]
                                );
        //}


        $this->addColumn('shipment_status', ['header' => __('Shipment'),
                        'index' => 'shipment_status',
                        'renderer' => 'Iksula\Ordersplit\Block\Adminhtml\Form\Renderer\Shipmentordersplit',
                        'filter' => false]
                        );

                        $this->addColumn('shipment_id', ['header' => __('Shipment Id'),
                                        'index' => 'shipment_id',
                                        'renderer' => 'Iksula\Ordersplit\Block\Adminhtml\Form\Renderer\Shipmentgettrackingnumber',
                                        'filter' => false]
                                        );

                                        $this->addColumn('awb_link', ['header' => __('AWB Link to Download'),
                                                        'index' => 'awb_link',
                                                        'renderer' => 'Iksula\Ordersplit\Block\Adminhtml\Form\Renderer\Awblinkdownload',
                                                        'filter' => false]
                                                        );





        $this->addColumn('invoice', ['header' => __('Invoice'),
                        'index' => 'invoice',
                        'renderer' => 'Iksula\Ordersplit\Block\Adminhtml\Form\Renderer\Invoiceordersplit',
                        'filter' => false]
                        );


                        $this->addColumn('shipment', ['header' => __('Master Invoice'),
                                        'index' => 'shipment',
                                        'renderer' => 'Iksula\Ordersplit\Block\Adminhtml\Form\Renderer\Masterinvoicecreation',
                                        'filter' => false]
                                        );


                        $this->addColumn('invoiced_status', ['header' => __('Picklist Download'),
                                        'index' => 'invoiced_status',
                                        'renderer' => 'Iksula\Ordersplit\Block\Adminhtml\Form\Renderer\Picklistdownload',
                                        'filter' => false]
                                        );

                                        $this->addColumn('invoice_id', ['header' => __('Invoice Id'),
                                                        'index' => 'invoice_id']
                                                        );


                                        $this->addColumn('picklist_sent', ['header' => __('Picklist Sent'),
                                                        'index' => 'picklist_sent',
                                                        'type' => 'options',
                                        								'options' => \Iksula\Ordersplit\Block\Adminhtml\Ordersplits\Grid::getOptionArray13()
                                                      ]
                                                        );



				// $this->addColumn(
				// 	'order_status',
				// 	[
				// 		'header' => __('Order Status'),
				// 		'index' => 'order_status',
				// 	]
				// );

				// $this->addColumn(
				// 	'invoice',
				// 	[
				// 		'header' => __('Invoice'),
				// 		'index' => 'invoice',
				// 	]
				// );
        //
				// $this->addColumn(
				// 	'shipment',
				// 	[
				// 		'header' => __('Shipment'),
				// 		'index' => 'shipment',
				// 	]
				// );

				$this->addColumn(
					'allocated_storeids',
					[
						'header' => __('Allocated Store Code'),
						'index' => 'allocated_storeids',
            'renderer' => 'Iksula\Ordersplit\Block\Adminhtml\Form\Renderer\Allocatedstorename',
            'filter' => false

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



		   $this->addExportType($this->getUrl('ordersplit/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('ordersplit/*/exportExcel', ['_current' => true]),__('Excel XML'));

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
        //$this->getMassactionBlock()->setTemplate('Iksula_Ordersplit::ordersplits/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('ordersplits');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('ordersplit/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('ordersplit/*/massStatus', ['_current' => true]),
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
        return $this->getUrl('ordersplit/*/index', ['_current' => true]);
    }

    /**
     * @param \Iksula\Ordersplit\Model\ordersplits|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {

      return false;

        return $this->getUrl(
            'ordersplit/*/edit',
            ['id' => $row->getId()]
        );

    }

    static public function getOptionArray13()
		{
            $data_array=array();
			$data_array[1]='Yes';
			$data_array[0]='No';
            return($data_array);
		}


}
