<?php
namespace Iksula\Ordersplit\Block\Adminhtml\Form\Renderer;

use Magento\Framework\DataObject;


class Allocatedstorename extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $ordersplitFactory;
    protected $storemanagerFactory;

    public function __construct(\Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory ,
                                \Iksula\Storemanager\Model\StoremanagerFactory $storemanagerFactory)
    {

      $this->ordersplitFactory = $ordersplitFactory;
      $this->storemanagerFactory = $storemanagerFactory;


    }
    public function render(DataObject $row)
    {
        $rowId = $row->getId();
        $allocatedstoreid = $this->ordersplitFactory->create()->load($rowId)->getAllocatedStoreids();

        $store_code = $this->storemanagerFactory->create()->load($allocatedstoreid)->getStoreCode();

        //$storeCat = $this->categoryFactory->create()->load($mageCateId);
        return $store_code;
    }
}
