<?php

namespace Iksula\Report\Model\ResourceModel\Reports;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    //protected $_idFieldName = 'id';
    public function __construct(
    \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
    \Psr\Log\LoggerInterface $logger,
    \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
    \Magento\Framework\Event\ManagerInterface $eventManager,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
    \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_init('Iksula\Report\Model\Reports','Iksula\Report\Model\ResourceModel\Reports');
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection,$resource);
        $this->storeManager = $storeManager;
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        // ->columns(array('main_table.entity_id'));
        //$this->getSelect()->reset(Zend_Db_Select::COLUMNS);
        //SELECT t.*, FROM YOUR_TABLE t, (SELECT @rownum := 0)
        /*$this->getSelect()->joinLeft(
        ['secondTable' => $this->getTable('sales_order_item')],'main_table.entity_id = secondTable.order_id',['main_table.increment_id','main_table.coupon_code', 'secondTable.item_id', 'secondTable.sku', 'secondTable.name', 'secondTable.price', 'secondTable.original_price', 'secondTable.qty_ordered', 'secondTable.discount_amount as coupon_amount','main_table.credit_amount'])
        ->joinLeft(
        ['thirdTable' => $this->getTable('sales_custom_attributes')],'secondTable.item_id = thirdTable.sales_item_id',['thirdTable.order_week','thirdTable.sales_item_id', 'thirdTable.brand','thirdTable.dimensions','thirdTable.weight','thirdTable.height','thirdTable.width','thirdTable.depth','thirdTable.color','thirdTable.discount_amount','thirdTable.discount_percent','thirdTable.product_code','thirdTable.delivery_store_no','thirdTable.delivery_store_name','thirdTable.shipping_address','thirdTable.country_name','thirdTable.amount_refund_user','thirdTable.sub_payment_mode','thirdTable.payment_title'])
       ->joinLeft(
        ['fourthTable' => $this->getTable('ordersplit_dup')],'secondTable.item_id = fourthTable.sales_order_item_id',['fourthTable.order_item_id','fourthTable.order_item_status', 'fourthTable.product_sku','fourthTable.sale_inventory','fourthTable.track_number'])
        ->joinLeft(
        ['fifthTable' => $this->getTable('storemanager')],'fifthTable.storemanager_id = fourthTable.allocated_storeids',['fifthTable.store_code','fifthTable.store_name'])
        ->joinLeft(
        ['sixthTable' => $this->getTable('sales_shipment')],'main_table.increment_id = sixthTable.increment_id',['sixthTable.updated_at']
        )
        ->joinLeft(
        ['seventhTable' => $this->getTable('sales_order_address')],'main_table.entity_id = seventhTable.parent_id',['seventhTable.firstname','seventhTable.lastname','seventhTable.telephone','seventhTable.street','seventhTable.region','seventhTable.city']
        )
         ->joinLeft(
        ['ninthTable' => $this->getTable('customer_entity')],'ninthTable.entity_id = main_table.customer_id',['ninthTable.email','ninthTable.default_billing']
        )
         ->joinLeft(
        ['eightTable' => $this->getTable('customer_address_entity')],'eightTable.entity_id = ninthTable.default_billing',['eightTable.country_id','eightTable.city as customer_city','eightTable.region as customer_region','eightTable.street as customer_street']
        )
        ->joinLeft(
        ['tenthTable' => $this->getTable('sales_order_payment')],'tenthTable.parent_id = main_table.entity_id',['tenthTable.method']
        )
        ->joinLeft(
        ['eleventhTable' => $this->getTable('sales_invoice')],'eleventhTable.order_id = main_table.entity_id',['eleventhTable.increment_id as invoice_no', 'eleventhTable.created_at as invoice_date']
        )->where(
            'seventhTable.address_type="shipping"'
        );*/

        $this->getSelect()->joinLeft(
        ['secondTable' => $this->getTable('sales_order_item')],'secondTable.item_id = main_table.sales_order_item_id',['main_table.order_item_id','main_table.order_item_status', 'main_table.product_sku','main_table.sale_inventory','main_table.track_number','main_table.tax_amount as tax_amount_value'])
        ->joinLeft(
        ['twelthTable' => $this->getTable('sales_order')],'twelthTable.entity_id = secondTable.order_id',['twelthTable.increment_id','twelthTable.coupon_code', 'secondTable.item_id', 'secondTable.sku', 'secondTable.name', 'secondTable.price', 'secondTable.original_price', 'secondTable.qty_ordered', 'secondTable.discount_amount as coupon_amount','twelthTable.credit_amount'])
        ->joinLeft(
        ['thirdTable' => $this->getTable('sales_custom_attributes')],'secondTable.item_id = thirdTable.sales_item_id',['thirdTable.order_week','thirdTable.sales_item_id', 'thirdTable.brand','thirdTable.dimensions','thirdTable.weight','thirdTable.height','thirdTable.width','thirdTable.depth','thirdTable.color','thirdTable.discount_amount','thirdTable.discount_percent','thirdTable.product_code','thirdTable.delivery_store_no','thirdTable.delivery_store_name','thirdTable.shipping_address','thirdTable.country_name','thirdTable.amount_refund_user','thirdTable.sub_payment_mode','thirdTable.payment_title'])
        ->joinLeft(
        ['fifthTable' => $this->getTable('storemanager')],'fifthTable.storemanager_id = main_table.allocated_storeids',['fifthTable.store_code','fifthTable.store_name'])
        ->joinLeft(
        ['sixthTable' => $this->getTable('sales_shipment')],'twelthTable.increment_id = sixthTable.increment_id',['sixthTable.updated_at'])
        ->joinLeft(
        ['seventhTable' => $this->getTable('sales_order_address')],'twelthTable.entity_id = seventhTable.parent_id',['seventhTable.firstname','seventhTable.lastname','seventhTable.telephone','seventhTable.street','seventhTable.region','seventhTable.city']
        )
         ->joinLeft(
        ['ninthTable' => $this->getTable('customer_entity')],'ninthTable.entity_id = twelthTable.customer_id',['ninthTable.email','ninthTable.default_billing']
        )
         ->joinLeft(
        ['eightTable' => $this->getTable('customer_address_entity')],'eightTable.entity_id = ninthTable.default_billing',['eightTable.country_id','eightTable.city as customer_city','eightTable.region as customer_region','eightTable.street as customer_street']
        )
        ->joinLeft(
        ['tenthTable' => $this->getTable('sales_order_payment')],'tenthTable.parent_id = twelthTable.entity_id',['tenthTable.method']
        )
        ->joinLeft(
        ['eleventhTable' => $this->getTable('sales_invoice')],'eleventhTable.order_id = twelthTable.entity_id',['eleventhTable.increment_id as invoice_no', 'eleventhTable.created_at as invoice_date']
        )->where(
            'seventhTable.address_type="shipping"'
        );

        //echo (string)$this->getSelect();
       // exit;

    }
}

?>
