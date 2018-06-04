<?php
namespace Iksula\Storemanager\Controller\Adminhtml\storemanager;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('storemanager_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                // init model and delete
                $model_data = $this->_objectManager->create('Iksula\Storemanager\Model\Storemanager');
                $model_data->load($id);                

                $user_data = $this->_objectManager->create('\Magento\User\Model\UserFactory');
                $role_data = $this->_objectManager->create('\Magento\Authorization\Model\RoleFactory');
                $role_mapping = $model_data->getRoleIdMapping();


                
                    if($role_mapping){

                        $Usercollection = $role_data->create()->load($role_mapping , 'parent_id');
                        $user_id = $Usercollection->getUserId();                                    
                        $userModel = $user_data->create()->load($user_id)->delete();   
                        $roleCollection = $role_data->create()->load($role_mapping)->delete();

                    }
                $model = $this->_objectManager->create('Iksula\Storemanager\Model\Storemanager');
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('The item has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['storemanager_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a item to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}