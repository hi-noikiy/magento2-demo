<?php

namespace Iksula\Orderreturns\Block\Adminhtml\Orderreturn\Edit\Tab;

/**
 * Orderreturn edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Iksula\Orderreturns\Model\Status
     */
    protected $_status;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Iksula\Orderreturns\Model\Status $status,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Iksula\Orderreturns\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('orderreturn');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);
        $collStatus = $model->getReturnStatus();

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

						
						
        $fieldset->addField(
            'return_status',
            'select',
            [
                'label' => __(' Return Status'),
                'title' => __(' Return Status'),
                'name' => 'return_status',
				'required' => true,
                'options' => \Iksula\Orderreturns\Block\Adminhtml\Orderreturn\Grid::getReturnStatus($collStatus),
                'disabled' => $isElementDisabled
            ]
        );
						
						
        $fieldset->addField(
            'comment',
            'textarea',
            [
                'name' => 'comment',
                'label' => __('Comments'),
                'title' => __('Comments'),
				// 'required' => true,
                'disabled' => $isElementDisabled
            ]
        );


        
        if($collStatus==0){
            
            $fieldset->addField(
                'pickup_time',
                'time',
                [
                    'name' => 'pickup_time',
                    'label' => __('Pickup Time'),
                    'title' => __('Pickup Time'),
                    'required' => true,
                    'disabled' => $isElementDisabled
                ]
            );      

            $dateFormat = $this->_localeDate->getDateFormat(
                \IntlDateFormatter::MEDIUM
            );
            $timeFormat = $this->_localeDate->getTimeFormat(
                \IntlDateFormatter::MEDIUM
            );

            $nextdate = $fieldset->addField(
                'pickup_date',
                'date',
                [
                    'name' => 'pickup_date',
                    'label' => __('Pickup Date'),
                    'title' => __('Pickup Date'),
                        'date_format' => $dateFormat,
                        //'time_format' => $timeFormat,
                    'required' => true,
                    'disabled' => $isElementDisabled
                ]
            );
            $nextdate->setAfterElementHtml("
                        <script type=\"text/javascript\">
                        //<![CDATA[
                       function disabledDate(date){
    var now= new Date();
    if(date.getFullYear()   <   now.getFullYear())  { return true; }
    if(date.getFullYear()   ==  now.getFullYear())  { if(date.getMonth()    <   now.getMonth()) { return true; } }
    if(date.getMonth()      ==  now.getMonth())     { if(date.getDate()     <   now.getDate())  { return true; } }
    if (date.getDay() == 0 || date.getDay() == 6) {
            return true;
        } else {
            return false;
        }
};

Calendar.setup({
    cont: 'datepicker',
    inputField : 'datepicker',
    button : '_datepicker',
    dateStatusFunc : disabledDate ,
});
                        //]]>
                        </script>");
                        
        }           
                        
                        

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);
		
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Item Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Item Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    
    public function getTargetOptionArray(){
    	return array(
    				'_self' => "Self",
					'_blank' => "New Page",
    				);
    }
}
