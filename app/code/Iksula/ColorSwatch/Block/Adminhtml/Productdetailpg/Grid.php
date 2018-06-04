<?php
namespace Iksula\ColorSwatch\Block\Adminhtml\Productdetailpg;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Iksula\ColorSwatch\Model\productdetailpgFactory
     */
    protected $_productdetailpgFactory;

    /**
     * @var \Iksula\ColorSwatch\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Iksula\ColorSwatch\Model\productdetailpgFactory $productdetailpgFactory
     * @param \Iksula\ColorSwatch\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Iksula\ColorSwatch\Model\ProductdetailpgFactory $ProductdetailpgFactory,
        \Iksula\ColorSwatch\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_productdetailpgFactory = $ProductdetailpgFactory;
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
        $this->setDefaultSort('swatch_id');
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
        $collection = $this->_productdetailpgFactory->create()->getCollection();
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
            'swatch_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'swatch_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
						
		$this->addColumn(
			'product_color',
			[
				'header' => __('Color'),
				'index' => 'product_color',
				'type' => 'options',
				'options' => \Iksula\ColorSwatch\Block\Adminhtml\Productdetailpg\Grid::getOptionArray0()
			]
		);
		
	   $this->addExportType($this->getUrl('colorswatch/*/exportCsv', ['_current' => true]),__('CSV'));
	   $this->addExportType($this->getUrl('colorswatch/*/exportExcel', ['_current' => true]),__('Excel XML'));

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

        $this->setMassactionIdField('swatch_id');
        //$this->getMassactionBlock()->setTemplate('Iksula_ColorSwatch::productdetailpg/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('productdetailpg');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('colorswatch/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('colorswatch/*/massStatus', ['_current' => true]),
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
        return $this->getUrl('colorswatch/*/index', ['_current' => true]);
    }

    /**
     * @param \Iksula\ColorSwatch\Model\productdetailpg|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'colorswatch/*/edit',
            ['swatch_id' => $row->getId()]
        );
		
    }

	
		/*static public function getOptionArray0()
		{
            $data_array=array(); 
			$data_array[0]='Blue';
			$data_array[1]='Pink';
            return($data_array);
		}
		static public function getValueArray0()
		{
            $data_array=array();
			foreach(\Iksula\ColorSwatch\Block\Adminhtml\Productdetailpg\Grid::getOptionArray0() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}*/
        static public function getOptionArray0()
        {        
            $attributeCode = 'product_color';
            $entityType = 'catalog_product';
            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
            $attributeInfo = $objectManager->get(\Magento\Eav\Model\Entity\Attribute::class)
                                   ->loadByCode($entityType, $attributeCode);

            $attributeId = $attributeInfo->getAttributeId();
            $attributeOptionAll = $objectManager->get(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class) ->setPositionOrder('asc')->setAttributeFilter($attributeId)->setStoreFilter()->load();
            
             $data_array=array();
             $color_option = $attributeOptionAll->getData();
             
             $color_count = count($color_option);
             for($i=0; $i<$color_count; $i++)
             {
                $color = $color_option[$i]['value'];
                $data_array[$color]=$color;
             }

            return($data_array);
        }

        static public function getValueArray0()
        {
            $data_array=array();
            foreach(\Iksula\ColorSwatch\Block\Adminhtml\Productdetailpg\Grid::getOptionArray0() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);        
            }
            return($data_array);

        }
		

}