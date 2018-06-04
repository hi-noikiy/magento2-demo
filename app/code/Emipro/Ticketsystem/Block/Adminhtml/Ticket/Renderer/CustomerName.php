<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Ticket\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class CustomerName extends AbstractRenderer {

    public function __construct(\Magento\Backend\Block\Context $context, StoreManagerInterface $storemanager, array $data = []) {
        $this->_storeManager = $storemanager;
        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
    }

    public function render(DataObject $row) {
        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerModel = $object_manager->get('Magento\Customer\Model\Customer');
        $ticketModel = $object_manager->get('Emipro\Ticketsystem\Model\TicketSystem');

        $Id = $row->getCustomerId();
        if ($Id) {
            $customer = $customerModel->load($Id);
            $FirstName = $customer->getFirstname();
            $LastName = $customer->getLastname();
            $name = $FirstName . " " . $LastName;
            $link = "<a href=" . $this->getUrl('customer/index/edit/', ['id' => $customer->getId(), '_secure' => true]) . " target='_blank'>" . $name . "</a>";
        } else {
            $collection = $ticketModel->load($row->getId());
            $name = $collection->getCustomerName();
            $link = $name;
        }

        return $link;
    }

}
