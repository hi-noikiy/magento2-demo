<?php

namespace Emipro\Ticketsystem\Block;

use Magento\Framework\View\Element\Template;

class Ticket extends Template {

    public function __construct(Template\Context $context, array $data = []) {
        parent::__construct($context, $data);
    }

    public function getSaveUrl() {
        return $this->getUrl('ticketsystem/index/save', ['_secure' => true]);
    }

    public function getBackUrl() {
        return $this->getUrl("ticketsystem/*/tickethistory", ['_secure' => true]);
    }

}
