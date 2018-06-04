<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksula\MyAccount\Controller\Address;

class Delete extends \Magento\Customer\Controller\Address
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $addressId = $this->getRequest()->getParam('id', false);
        if ($addressId) {
            $address = $this->_addressRepository->getById($addressId);
            $address->getCustomerId();
            $this->_getSession()->getCustomerId(); 
            if ($address->getCustomerId() === $this->_getSession()->getCustomerId()) {
                $this->_addressRepository->deleteById($addressId);
                $this->messageManager->addSuccess(__('You deleted the address .'));
            } else {
                $this->messageManager->addError(__('We can\'t delete the address right now inside.'));
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
