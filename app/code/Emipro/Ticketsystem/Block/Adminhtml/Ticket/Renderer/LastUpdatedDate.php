<?php

/*
 * //////////////////////////////////////////////////////////////////////////////////////
 *
 * @author Emipro Technologies
 * @Category Emipro
 * @package Emipro_Ticketsystem
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * //////////////////////////////////////////////////////////////////////////////////////
 */

namespace Emipro\Ticketsystem\Block\Adminhtml\Ticket\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class LastUpdatedDate extends AbstractRenderer {

    public function __construct(\Magento\Backend\Block\Context $context, array $data = []) {
        
        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
    }
    
    public function render(DataObject $row) {
        
        $Id = $row->getTicketId();
        if ($row->getLastupdatedDate() != NULL) {
            $date = date_create($row->getLastupdatedDate());
            return date_format($date,"j M, Y h:i A");
        } else {
            return false;
        }
    }

}