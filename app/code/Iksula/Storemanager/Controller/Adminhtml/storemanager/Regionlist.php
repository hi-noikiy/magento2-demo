<?php
namespace Iksula\Storemanager\Controller\Adminhtml\storemanager;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Regionlist extends \Magento\Backend\App\Action
{


    protected $resultPageFactory;
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    
    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context,
        \Magento\Directory\Model\CountryFactory $countryFactory,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory
            )
    {

            $this->_countryFactory = $countryFactory;
            $this->resultPageFactory = $resultPageFactory;
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
        $state = "<option value=''>--Please Select--</option>";
        if ($countrycode != '') {
            $statearray =$this->_countryFactory->create()->setId(
                    $countrycode
                )->getLoadedRegionCollection()->toOptionArray();

            foreach ($statearray as $_state) {
                if($_state['value']){
                    $state .= "<option value='".$_state['value']."'>" . $_state['label'] . "</option>";
            }
           }
        }
       $result['htmlconent']=$state;
         $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }
}