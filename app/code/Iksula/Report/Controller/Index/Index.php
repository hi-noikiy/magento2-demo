<?php

namespace Iksula\Report\Controller\Index;
use Magento\Framework\App\Action\Context;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_objectManager;

    public function __construct(Context $context, \Magento\Framework\ObjectManagerInterface $objectManager) {
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }

    public function execute()
    {
		$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();

    	//http://192.168.4.243/2XL/report/index/index
		
		$tableName = $resource->getTableName('ordersplits_tb'); //gives table name with prefix
		$table_name = $resource->getTableName('ordersplit_dup');
        $shipment_table_name = $resource->getTableName('sales_shipment');
        $shipment_track_table_name = $resource->getTableName('sales_shipment_track');

		$sql_del = "TRUNCATE table " . $table_name;
		$connection->query($sql_del);
		 
		//Select Data from table
		$sql = "select * FROM " . $tableName;
		$result = $connection->fetchAll($sql);

		for($i=0; $i<count($result); $i++)
		{

			$order_id = @$result[$i]['order_id'];						
			$order_item_id = @$result[$i]['order_item_id'];
			$picklist_id = @$result[$i]['picklist_id'];
			$order_date = @$result[$i]['order_date'];
			$sku = @$result[$i]['sku'];
			$order_item_status = @$result[$i]['order_item_status'];
			$order_status = @$result[$i]['order_status'];
			$invoice = @$result[$i]['invoice'];
			$shipment = @$result[$i]['shipment'];
			$allocated_storeids = @$result[$i]['allocated_storeids'];
			$order_items_data = json_decode(@$result[$i]['order_items_data'], true);
            $shipment_id = @$result[$i]['shipment_id'];
            $inventory = @$result[$i]['inventory'];                        
            $invoiced_status = @$result[$i]['invoiced_status'];
            $invoice_id = @$result[$i]['invoice_id'];
            $picklist_sent = @$result[$i]['picklist_sent'];
            
            $shipment_status = @$result[$i]['shipment_status'];

            $sql_shipment = "select entity_id FROM ".$shipment_table_name." where increment_id='".$shipment_id."'";
            $result_shipment = $connection->fetchAll($sql_shipment);
            $sales_shipment_id = @$order_items_data[0]['entity_id'];

            $sql_track = "select entity_id FROM ".$shipment_track_table_name." where parent_id='".$sales_shipment_id."'";
            $result_track = $connection->fetchAll($sql_track);
            $track_number = @$result_track[0]['track_number'];

	    	for($j=0; $j<count($order_items_data); $j++)
	    	{
				$sales_order_item_id = $order_items_data[$j]['order_items_id'];
				$product_sku = $order_items_data[$j]['sku'];
				$sale_inventory = $order_items_data[$j]['inventory'];

				$sel_order = "select tax_amount, qty_ordered FROM sales_order_item where order_id='".$order_id."' and item_id='".$sales_order_item_id."'";
				$result_order = $connection->fetchAll($sel_order);
        		$tax_amount = @$result_order[0]['tax_amount'];
        		$qty_ordered = @$result_order[0]['qty_ordered'];
        		$single_quantity_tax_amount = ($tax_amount/$qty_ordered);
        		$tax_amount_value = $single_quantity_tax_amount * $sale_inventory;

				$sql_ins = "Insert Into " . $table_name . " (order_id, order_item_id, picklist_id, order_date, sku, order_item_status, order_status, invoice, shipment, allocated_storeids, product_sku, sales_order_item_id, sale_inventory,track_number,tax_amount) Values ('".$order_id."','".$order_item_id."','".$picklist_id."','".$order_date."','".$sku."','".$order_item_status."','".$order_status."','".$invoice."','".$shipment."','".$allocated_storeids."','".$product_sku."','".$sales_order_item_id."','".$sale_inventory."','".$track_number."','".$tax_amount_value."')";
				$connection->query($sql_ins);
			}		
			
		}
    
    }
}