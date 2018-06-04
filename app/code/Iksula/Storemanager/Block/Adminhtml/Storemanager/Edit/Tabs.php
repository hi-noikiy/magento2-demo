<?php
namespace Iksula\Storemanager\Block\Adminhtml\Storemanager\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('storemanager_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Storemanager Information'));
    }

    protected function _prepareLayout()
    {       

        
             $this->addTab(
                'form_storemanager',
                array(
                    'label'   => __('Store Manager Information'),
                    'title'   => __('Store Manager Information'),
                    'content' => $this->getLayout()->createBlock(
                        'Iksula\Storemanager\Block\Adminhtml\Storemanager\Edit\Tab\Main'
                    )
                    ->toHtml(),
                )
            );

             $this->addTab(
                'form_user_credential',
                array(
                    'label'   => __('User Details'),
                    'title'   => __('User Details'),
                    'content' => $this->getLayout()->createBlock(
                        'Iksula\Storemanager\Block\Adminhtml\Storemanager\Edit\Tab\Usercredential'
                    )
                    ->toHtml(),
                )
            );

             $this->addTab(
                'form_role_resource',
                array(
                    'label'   => __('Role Resource'),
                    'title'   => __('Role Resource'),
                    'content' => $this->getLayout()->createBlock(
                        'Iksula\Storemanager\Block\Role\Tab\Edit\Roleresource'
                    )
                    ->toHtml(),
                )
            );
    
        
        return parent::_prepareLayout();
    }
}