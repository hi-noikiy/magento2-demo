<?php

namespace Iksula\Networkonlinepayment\Controller\Standard;

class Cancel extends \Iksula\Networkonlinepayment\Controller\NetworkAbstract {

    public function execute() {
        $this->getOrder()->cancel()->save();
        
        $this->messageManager->addErrorMessage(__('Your order has been can cancelled'));
        $this->getResponse()->setRedirect(
                $this->getCheckoutHelper()->getUrl('checkout')
        );
    }

}
