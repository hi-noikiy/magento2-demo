<?php
namespace Iksula\Ordersplit\Block\Adminhtml\Form\Renderer;

use Magento\Framework\DataObject;


class Awblinkdownload extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $ordersplitFactory;
    protected $storemanagerFactory;
    protected $_backendUrl;

    public function __construct(\Iksula\Ordersplit\Model\OrdersplitsFactory $ordersplitFactory ,
                                \Iksula\Storemanager\Model\StoremanagerFactory $storemanagerFactory,
                                \Magento\Backend\Model\UrlInterface $backendUrl
                                )
    {

      $this->ordersplitFactory = $ordersplitFactory;
      $this->storemanagerFactory = $storemanagerFactory;
      $this->_backendUrl = $backendUrl;


    }
    public function render(DataObject $row)
    {

      $rowId = $row->getId();
      $ordersplit_row = $this->ordersplitFactory->create()->load($rowId);
      $awb_link = $ordersplit_row->getAwbLink();
      $result = "";
      if(isset($awb_link) && ($awb_link != "")){

        $result .= '<div>
            <a href="'.$awb_link.'" download>Awb Link Download</a>
            </div>';
      }
      return $result;
    }
}
