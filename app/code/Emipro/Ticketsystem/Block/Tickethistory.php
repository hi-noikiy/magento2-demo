<?php
namespace Emipro\Ticketsystem\Block;

use Emipro\Ticketsystem\Model\ResourceModel\TicketSystem\CollectionFactory;


class Tickethistory extends \Magento\Framework\View\Element\Template {

    protected $_ticket;
    protected $customerSession;
    protected $ticketCollection;
    protected $_resource;
    protected $_orderCollectionFactory;

    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, 
    \Magento\Customer\Model\Session $customerSession, 
    \Magento\Framework\App\ResourceConnection $resource,
    \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $OrderCollectionFactory,
    CollectionFactory $ticketFactory, array $data = []
    ) {
        $this->_ticket = $ticketFactory;
        $this->customerSession = $customerSession;
          $this->_resource = $resource;
          $this->_orderCollectionFactory=$OrderCollectionFactory;
          
        parent::__construct($context,$data);
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
        if ($this->getAllTickets()) {
            $pager = $this->getLayout()->createBlock(
                            'Magento\Theme\Block\Html\Pager', 'ticket.history.pager'
                    )->setCollection(
                    $this->getAllTickets()
            );
            $this->setChild('pager', $pager);
            $this->getAllTickets()->load();
        }
        return $this;
    }

    public function getAllTickets() {
        $customerId = $this->customerSession->getId();
        if (!$this->ticketCollection) {
            $this->ticketCollection = $this->_ticket->create();
			$this->ticketCollection->getAllTickets($customerId);
        }
        return $this->ticketCollection;
    }

    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }

    public function getViewUrl($ticket) {
        return $this->getUrl('ticketsystem/index/view', ['id' => $ticket->getId(), '_secure' => true]);
    }
}
