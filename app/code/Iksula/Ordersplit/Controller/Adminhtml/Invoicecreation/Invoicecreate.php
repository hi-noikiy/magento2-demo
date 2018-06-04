<?php
namespace Iksula\Ordersplit\Controller\Adminhtml\Invoicecreation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class  Invoicecreate extends Action
{

    protected $_resultPageFactory;
    protected $ordersplitFactory;
    protected $_orderRepository;
    protected $_invoiceService;
    protected $transaction;
    protected $salesOrderItemsFactory;



    public function __construct(Context $context,PageFactory $resultPageFactory,
                                \Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory,
                                \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
                                \Magento\Sales\Model\Service\InvoiceService $invoiceService,
                                \Magento\Framework\DB\Transaction $transaction,
                                \Magento\Sales\Model\Order\ItemFactory $salesOrderItemsFactory) {

        $this->_resultPageFactory = $resultPageFactory;
        $this->ordersplitFactory = $ordersplitFactory;
        $this->_orderRepository = $orderRepository;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->salesOrderItemsFactory = $salesOrderItemsFactory;

        parent::__construct($context);
    }

    public function execute()
    {

      $order_row_itemid = $this->getRequest()->getParam('order_itemrowid');
      $ordersplitobj = $this->ordersplitFactory->create()->load($order_row_itemid);
      $order_items_data = $ordersplitobj->getOrderItemsData();
      $orderId = $ordersplitobj->getOrderId();

      $aOrderItemsData = json_decode($order_items_data , true);

      $itemsArray = array();

       $order = $this->_orderRepository->get($orderId);
       $invoicecoll = $order->getInvoiceCollection();
       $order_invoice_cnt = $invoicecoll->count();
       $shippingAmount = $order->getBaseShippingAmount();
       $GrandTotalSubmition = 0;
       $Subtotal = 0;
       $BaseSubtotal = 0;
       $shippingamountordersplit = 0;
       $taxamountordersplit = 0;

      foreach($aOrderItemsData as $key => $OrderitemsValues){
          $itemsArray [$OrderitemsValues['order_items_id']] =  $OrderitemsValues['inventory'];
          $aOrderItemsTotal = $this->getInvoiceDetails($OrderitemsValues['order_items_id'] , $OrderitemsValues['inventory'] , $shippingAmount,$order_invoice_cnt);
          $GrandTotalSubmition += ($aOrderItemsTotal['base_row_total'] + $aOrderItemsTotal['shipping_amount_divided']);


          $Subtotal += $aOrderItemsTotal['row_total'];
          $BaseSubtotal += $aOrderItemsTotal['base_row_total'];
          $shippingamountordersplit += $aOrderItemsTotal['shipping_amount_divided'];
          $taxamountordersplit += $aOrderItemsTotal['base_tax_amount'];

      }


       //if($order->canInvoice()) {
         try{
           //$itemsArray = ['80'=>2]; //here 80 is order item id and 2 is it's quantity to be invoice

           $subTotal = $Subtotal;
           $baseSubtotal = $BaseSubtotal;
           $grandTotal = $GrandTotalSubmition;
           $baseGrandTotal = $GrandTotalSubmition;
           $iShippingAmountDiveded = $shippingamountordersplit;
           $SubTotal_exclTax = ($subTotal) - ($taxamountordersplit);

           $invoice = $this->_invoiceService->prepareInvoice($order, $itemsArray);
           $invoice->setShippingAmount($iShippingAmountDiveded);
           $invoice->setBaseShippingAmount($iShippingAmountDiveded);
           $invoice->setSubtotal($SubTotal_exclTax);
           $invoice->setBaseSubtotal($SubTotal_exclTax);
           $invoice->setGrandTotal($grandTotal);
           $invoice->setBaseGrandTotal($baseGrandTotal);
           $invoice->setTaxAmount($taxamountordersplit);
           $invoice->setBaseTaxAmount($taxamountordersplit);
           $invoice->register();
           $transactionSave = $this->_transaction->addObject(
               $invoice
           )->addObject(
               $invoice->getOrder()
           );
           $transactionSave->save();
           //$this->_invoiceService->send($invoice);
           //send notification code
           $order->addStatusHistoryComment(
               __('Notified customer about invoice #%1.', $invoice->getIncrementId())
           )
           ->setIsCustomerNotified(true)
           ->save();

           $ordersplitobj->setInvoiceId($invoice->getIncrementId());
           $ordersplitobj->setInvoicedStatus(1);
           $ordersplitobj->setOrderItemStatus('store_invoiced');
           $ordersplitobj->save();
         }catch (Exception $e){
              echo $e->getMessage();
              echo '<br />';
         }
            echo 'Invoice done';
       // }else{
       //   $ordersplitobj->setInvoicedStatus(1);
       //   $ordersplitobj->setOrderItemStatus('store_invoiced');
       //   $ordersplitobj->save();
       //   echo 'Invoice not done';
       // }

    }



    function getInvoiceDetails($order_item_id , $inventory , $shippingAmount , $order_invoice_cnt){


      $OrderItemsCollectionFactory = $this->salesOrderItemsFactory->create()->load($order_item_id);
      $ActualOriginalQtyOrderItems = (int)$OrderItemsCollectionFactory->getQtyOrdered();

      $srowTotal = round($OrderItemsCollectionFactory->getRowTotalInclTax());
      $sRowTotalDivided = ($srowTotal) / ($ActualOriginalQtyOrderItems);
      $sRowTotalBasedonOrdersplit = round($sRowTotalDivided) * ($inventory);


      $sBaseRowTotal = round($OrderItemsCollectionFactory->getBaseRowTotalInclTax());
      $sBaseRowTotalDivided = ($sBaseRowTotal) / ($ActualOriginalQtyOrderItems);
      $sBaseRowTotalBasedonOrdersplit = round($sBaseRowTotalDivided) * ($inventory);


      $sBaseTaxAmount = round($OrderItemsCollectionFactory->getTaxAmount());
      $sBaseTaxDivided = ($sBaseTaxAmount) / ($ActualOriginalQtyOrderItems);
      $sBaseTaxOrdersplit = round($sBaseTaxDivided) * ($inventory);


      $iRowTaxandRowTotal = ($sRowTotalBasedonOrdersplit) + ($sBaseTaxOrdersplit);
      $iBaseRowTaxandRowTotal = ($sBaseRowTotal) + ($sBaseTaxOrdersplit);


      //$sShippingAmountDivided = ($shippingAmount) / ($ActualOriginalQtyOrderItems);
      //$sShippingAmountOrdersplit = ($sShippingAmountDivided) * ($inventory);

      if($order_invoice_cnt == 0)
      {
          $sShippingAmountOrdersplit = $shippingAmount;
      }
      else
      {
          $sShippingAmountOrdersplit = 0;
      }



      $aOrderItemsData = array('row_total' => round($sRowTotalBasedonOrdersplit) ,
      'base_row_total' => round($sBaseRowTotalBasedonOrdersplit) , /*'row_total_taxplus' => round($iRowTaxandRowTotal) , 'base_row_total_taxplus' => round($iBaseRowTaxandRowTotal),*/ 'tax_amount' => $sBaseTaxOrdersplit , 'base_tax_amount' => $sBaseTaxOrdersplit ,
      'shipping_amount_divided' => $sShippingAmountOrdersplit);

      return $aOrderItemsData;

    }





}
