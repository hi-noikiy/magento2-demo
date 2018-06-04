<?php
namespace Iksula\Storemanager\Controller\Adminhtml\storemanager;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Regionlistselected extends \Magento\Backend\App\Action
{


    protected $resultPageFactory;
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;


    protected $_storemanagerFactory;

    
    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context,
        \Magento\Directory\Model\CountryFactory $countryFactory,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            \Iksula\Storemanager\Model\StoremanagerFactory $storemanagerFactory
            )
    {

            $this->_countryFactory = $countryFactory;
            $this->resultPageFactory = $resultPageFactory;
            $this->_storemanagerFactory = $storemanagerFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
         $countrycode = $this->getRequest()->getParam('country');
         $storemanagerid = $this->getRequest()->getParam('storemanager_id');

         $store_state = $this->_storemanagerFactory->create()->load($storemanagerid)->getStoreState();


        $state = "<option value=''>--Please Select--</option>";
        if ($countrycode != '') {
            $statearray = $this->_countryFactory->create()->setId(
                    $countrycode
                )->getLoadedRegionCollection()->toOptionArray();

            foreach ($statearray as $_state) {
                $selected = "";
                if($_state['value']){

                    if($store_state == $_state['value']){
                        $selected = 'selected="selected"';
                    }

                    $state .= "<option ".$selected." value='".$_state['value']."'>" . $_state['label'] . "</option>";
                }
           }
        }
       $result['htmlconent']=$state;
         $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }
}