<?php
namespace Iksula\Storemanager\Controller\Adminhtml\storemanager;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;


class Save extends \Magento\Backend\App\Action
{


    protected $_userFactory;

    private $roleFactory; 
     
    private $rulesFactory;
    
    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context , \Magento\User\Model\UserFactory $userFactory,
                                \Magento\Authorization\Model\RoleFactory $roleFactory, 
                                \Magento\Authorization\Model\RulesFactory $rulesFactory )
    {
        
        $this->_userFactory = $userFactory;
        $this->roleFactory = $roleFactory;
        $this->rulesFactory = $rulesFactory;        
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        

        /*************************************************************************************************************/

                        // Create a Role for same
                $role=$this->roleFactory->create();
                
                if(isset($data['role_id_mapping'])){                   
                    
                    $role_id = $data['role_id_mapping'];
                    
                }else{
                        $role->setPid(0) //set parent role id of your role
                            ->setRoleName($data['store_username'])
                            ->setRoleType(RoleGroup::ROLE_TYPE) 
                            ->setUserType(UserContextInterface::USER_TYPE_ADMIN);
                        $role->save();
                        $role_id = $role->getId();
                }
        
        /* Now we set that which resources we allow to this role */

        if(empty($data['resource'])){
            $data['resource'] = array('Magento_Backend::dashboard');        
        }        
        
        /* Array of resource ids which we want to allow this role*/
        $this->rulesFactory->create()->setRoleId($role_id)->setResources($data['resource'])->saveRel();

        /****************************************************************************************************/


        /**********************************************************************/
                //  Create a User according to the same

        $adminuser_data = array('username' => $data['store_username'] , 'firstname' => $data['first_name'] , 'lastname' => $data['last_name'] , 'email' => $data['store_emailid'] , 'password' => $data['password'] , 'interface_locale' => 'en_US' , 'is_active' => $data['store_status']);

        /*********************  Get user details using Role Details by Role Id Mapping columns ***********/

            
            if(isset($data['role_id_mapping'])){                
                $Usercollection = $this->roleFactory->create()->load($data['role_id_mapping'] , 'parent_id');
                $user_id = $Usercollection->getUserId();            
                $userModel = $this->_userFactory->create()->load($user_id);            
                $userModel->setFirstName($data['first_name']);
                $userModel->setLastName($data['last_name']);
                $userModel->setIsActive($data['store_status']);
                if(trim($data['password']) != ""){
                    $userModel->setPassword($data['password']);    
                }                
                
                $userModel->save(); 
                
            }else{
                
                $userModel = $this->_userFactory->create();                        
                $userModel->setData($adminuser_data);
                $userModel->setRoleId($role_id);
                $userModel->save(); 
            }
            

        if($role_id){
            $data['role_id_mapping'] = $role_id;
        }else{

            die('Role not found');
        }
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create('Iksula\Storemanager\Model\Storemanager');

            $id = $this->getRequest()->getParam('storemanager_id');
            if ($id) {
                $model->load($id);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            }
			try{
				$uploader = $this->_objectManager->create(
					'Magento\MediaStorage\Model\File\Uploader',
					['fileId' => 'image']
				);
				$uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
				/** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
				$imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
				$uploader->setAllowRenameFiles(true);
				$uploader->setFilesDispersion(true);
				/** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
				$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
					->getDirectoryRead(DirectoryList::MEDIA);
				$result = $uploader->save($mediaDirectory->getAbsolutePath('emizen_banner'));
					if($result['error']==0)
					{
						$data['image'] = 'emizen_banner' . $result['file'];
					}
			} catch (\Exception $e) {
				//unset($data['image']);
            }
			//var_dump($data);die;
			if(isset($data['image']['delete']) && $data['image']['delete'] == '1')
				$data['image'] = '';
			
            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Storemanager has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['storemanager_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Storemanager.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['storemanager_id' => $this->getRequest()->getParam('storemanager_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}