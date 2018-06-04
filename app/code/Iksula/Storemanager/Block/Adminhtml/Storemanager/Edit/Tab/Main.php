<?php

namespace Iksula\Storemanager\Block\Adminhtml\Storemanager\Edit\Tab;

/**
 * Storemanager edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Iksula\Storemanager\Model\Status
     */
    protected $_status;

    protected $_countryFactory;


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
        \Iksula\Storemanager\Model\Status $status,
        \Magento\Directory\Model\Config\Source\Country $countryFactory,

        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        $this->_countryFactory = $countryFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }



    public function getCountries(){

            $countries = $this->_countryFactory->toOptionArray(false, 'US');
            foreach($countries as $aCountries){

                $aCountriesData [$aCountries['value']] = $aCountries['label'];


            }
            return $aCountriesData ;


    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Iksula\Storemanager\Model\BlogPosts */



        //$countries = $this->_countryFactory->toOptionArray(false, 'US');
        /*$regionCollection = $this->_regionFactory->create()->getCollection()->addCountryFilter(
            $formData['country_id']
        );*/

        /*echo '<pre>';
        print_r($countries);*/
/*        echo 'jsjsjsj';
        exit;
*/
        $model = $this->_coreRegistry->registry('storemanager');

            $isElementDisabled = false;
            $store_code_array = [
                'name' => 'store_code',
                'label' => __('Store Code'),
                'title' => __('Store Code'),
                'required' => true,
                'class' => 'required-entry',
            ];

            $store_username_array = [
                'name' => 'store_username',
                'label' => __('Store Username'),
                'title' => __('Store Username'),
                'required' => true,
                'class' => 'required-entry',
                //'disabled' => $isUsernameFieldDisabled
            ];

            $store_emailid_array = [
                'name' => 'store_emailid',
                'label' => __('Store Email Id'),
                'title' => __('Store Email Id'),
                'required' => true,
                'class' => 'required-entry validate-emails',
                'disabled' => $isElementDisabled
            ];

        if($model->getId()){

            $isStorecodeFieldreadonly = array('readonly' => true);
            $store_code_array = array_merge($store_code_array , $isStorecodeFieldreadonly);
            $isStoreUsernameFieldreadonly = array('readonly' => true);
            $store_username_array = array_merge($store_username_array , $isStoreUsernameFieldreadonly);
            $isStoreEmailidFieldreadonly = array('readonly' => true);
            $store_emailid_array = array_merge($store_emailid_array , $isStoreEmailidFieldreadonly);

        }


        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Store Manager')]);

        if ($model->getId()) {
            $fieldset->addField('role_id_mapping', 'hidden', ['name' => 'role_id_mapping']);
            $fieldset->addField('storemanager_id', 'hidden', ['name' => 'storemanager_id']);
        }


        $fieldset->addField(
            'store_name',
            'text',
            [
                'name' => 'store_name',
                'label' => __('Store Name'),
                'title' => __('Store Name'),
                'required' => true,
                'class' => 'required-entry',

                //'disabled' => $isElementDisabled,

            ]
        );

        $fieldset->addField(
            'store_code',
            'text',
            $store_code_array

        );

        $fieldset->addField(
            'store_username',
            'text',
            $store_username_array

        );

        $fieldset->addField(
            'store_country',
            'select',
            [
                'name' => 'store_country',
                'label' => __('Store Country'),
                'title' => __('Store Country'),
                'options' => $this->getCountries(),
                'required' => true,
                'class' => 'required-entry',
                'disabled' => $isElementDisabled
            ]
        );

        $region = $fieldset->addField(
            'store_state',
            'select',
            [
                'name' => 'store_state',
                'label' => __('Store State'),
                'title' => __('Store State'),
                'required' => true,
                'class' => 'required-entry',
                //'options' => $this->getRegions(),

                'disabled' => $isElementDisabled
            ]
        );

         $region->setAfterElementHtml("
            <script type=\"text/javascript\">
                    require([
                    'jquery',
                    'mage/template',
                    'jquery/ui',
                    'mage/translate'
                ],
                function($, mageTemplate) {
                    var country_id = $('#page_store_country').val();
                    var storemanager_idval = '". $model->getId() ."';

                        if(storemanager_idval){
                            $.ajax({
                                   url : '". $this->getUrl('storemanager/storemanager/regionlistselected/') ."storemanager_id/'+storemanager_idval+'/country/'+country_id,
                                    type: 'get',
                                    dataType: 'json',
                                   showLoader:true,
                                   success: function(data){
                                        $('#page_store_state').empty();
                                        $('#page_store_state').append(data.htmlconent);
                                   }
                            });
                        }


                   $('#edit_form').on('change', '#page_store_country', function(event){

                        $.ajax({
                               url : '". $this->getUrl('storemanager/storemanager/regionlist/') . "country/' +  $('#page_store_country').val(),
                                type: 'get',
                                dataType: 'json',
                               showLoader:true,
                               success: function(data){
                                    $('#page_store_state').empty();
                                    $('#page_store_state').append(data.htmlconent);
                               }
                            });
                   });

                   $('input[name=\"store_username\"]').on('blur' , function(){

                        var store_username = $.trim($('input[name=\"store_username\"]').val());

                        if (!$('input[name=\"store_username\"]').is('[readonly]') ) {

                            if(store_username != ''){

                                $.ajax({
                                       url : '". $this->getUrl('storemanager/storemanager/checkusernameexist/') . "username/' +  store_username,
                                        type: 'get',
                                        dataType: 'json',
                                       showLoader:true,
                                       success: function(data){

                                            if(data.status == 0){
                                                alert(data.message);
                                                $('input[name=\"store_username\"]').val('');
                                            }

                                       }
                                });
                            }
                        }

                   });

                   $('input[name=\"store_emailid\"]').on('blur' , function(){

                        var store_emailid = $.trim($('input[name=\"store_emailid\"]').val());

                        if (!$('input[name=\"store_emailid\"]').is('[readonly]') ) {

                            if(store_emailid != ''){

                                $.ajax({
                                       url : '". $this->getUrl('storemanager/storemanager/checkuseremailidexist/') . "email_id/' +  store_emailid,
                                        type: 'get',
                                        dataType: 'json',
                                       showLoader:true,
                                       success: function(data){

                                            if(data.status == 0){
                                                alert(data.message);
                                                $('input[name=\"store_emailid\"]').val('');
                                            }

                                       }
                                });
                            }
                        }

                   });


                   $('input[name=\"store_code\"]').on('blur' , function(){

                        var store_code = $.trim($('input[name=\"store_code\"]').val());

                        if (!$('input[name=\"store_code\"]').is('[readonly]') ) {

                            if(store_code != ''){

                                $.ajax({
                                       url : '". $this->getUrl('storemanager/storemanager/checkstorecodeexist/') . "storecode/' +  store_code,
                                        type: 'get',
                                        dataType: 'json',
                                       showLoader:true,
                                       success: function(data){

                                            if(data.status == 0){
                                                alert(data.message);
                                                $('input[name=\"store_code\"]').val('');
                                            }

                                       }
                                });
                            }
                        }

                   });
                }

            );
            </script>"
        );



        /*$fieldset->addField(
            'store_city',
            'text',
            [
                'name' => 'store_city',
                'label' => __('Store City'),
                'title' => __('Store City'),
                'required' => true,
                'class' => 'required-entry',

                'disabled' => $isElementDisabled
            ]
        );*/

        $fieldset->addField(
            'store_pincode',
            'text',
            [
                'name' => 'store_pincode',
                'label' => __('Store Pincode'),
                'title' => __('Store Pincode'),
                'required' => true,
                'class' => 'required-entry validate-digits',

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'store_longitude',
            'text',
            [
                'name' => 'store_longitude',
                'label' => __('Store Longitude'),
                'title' => __('Store Longitude'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'store_latitude',
            'text',
            [
                'name' => 'store_latitude',
                'label' => __('Store Latitude'),
                'title' => __('Store Latitude'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'store_type',
            'select',
            [
                'name' => 'store_type',
                'label' => __('Store Type'),
                'title' => __('Store Type'),

                'options' => \Iksula\Storemanager\Block\Adminhtml\Storemanager\Grid::getOptionArrayStoreType(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'store_address',
            'textarea',
            [
                'name' => 'store_address',
                'label' => __('Store Address'),
                'title' => __('Store Address'),
                'required' => true,
                'class' => 'required-entry',

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'store_mobileno',
            'text',
            [
                'name' => 'store_mobileno',
                'label' => __('Store Mobile Number'),
                'title' => __('Store Mobile Number'),
                'required' => true,
                'class' => 'required-entry validate-digits',
                'maxlength' => 12,

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'store_emailid',
            'text',
            $store_emailid_array
        );


        $fieldset->addField(
            'store_status',
            'select',
            [
                'label' => __('Store Status'),
                'title' => __('Store Status'),
                'name' => 'store_status',

                'options' => \Iksula\Storemanager\Block\Adminhtml\Storemanager\Grid::getOptionArray13(),
                'disabled' => $isElementDisabled
            ]
        );

         $Lastfield = $form->getElement('store_status');
       $Lastfield->setAfterElementHtml(
                '<script>
                        require(["jquery"], function($){
                            $(document).ready(function(){

                            });

                        });
                </script>
                ');



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
        return __('Store Manager Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Store Manager Information');
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
