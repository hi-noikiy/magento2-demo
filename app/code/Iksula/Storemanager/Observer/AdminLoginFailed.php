<?php
namespace Iksula\Storemanager\Observer;

class AdminLoginFailed implements \Magento\Framework\Event\ObserverInterface
{


    protected $_storeManagerInterface;

  public function __construct(
                                 \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
  ){
          $this->_storeManagerInterface = $storeManagerInterface;
  }

  public function execute(\Magento\Framework\Event\Observer $observer)
  {
    echo "login failed";
    die();
     $this->_storeManagerInterface->setCurrentStore(1);
   
  }
}
