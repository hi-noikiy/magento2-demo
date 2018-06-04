<?php

namespace Emipro\Ticketsystem\Block\Adminhtml\Ticket\Customer;

use Magento\Backend\Block\Widget\Grid\Extended;

class Grid extends Extended {

    protected $_ticketSystemFactory;
    protected $_helper;
    protected $customer;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Emipro\Ticketsystem\Model\TicketSystemFactory $ticketSystemFactory, \Magento\Framework\Module\Manager $moduleManager, \Emipro\Ticketsystem\Helper\Data $helper, \Magento\Customer\Model\CustomerFactory $customer, array $data = []
    ) {
        $this->_helper = $helper;
        $this->_ticketSystemFactory = $ticketSystemFactory;
        $this->moduleManager = $moduleManager;
        $this->customer = $customer;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct() {
        parent::_construct();
        $this->setId('gridGrid');
        $this->setDefaultSort('ticket_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection() {
        $collection = $this->customer->create()->getCollection()
                ->addNameToSelect()
                ->addAttributeToSelect('email')
                ->addAttributeToSelect('created_at')
                ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
                ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
                ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
                ->joinAttribute('billing_regione', 'customer_address/region', 'default_billing', null, 'left')
                ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns() {
        $this->addColumn(
                'entity_id', [
            'header' => __('ID'),
            'type' => 'number',
            'index' => 'entity_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
                ]
        );
        $this->addColumn(
                'name', [
            'header' => __('Name'),
            'index' => 'name',
                ]
        );
        $this->addColumn(
                'email', [
            'header' => __('Email'),
            'index' => 'email'
                ]
        );

        $this->addColumn(
                'telephone', [
            'header' => __('Telephone'),
            'index' => 'billing_telephone',
            'type' => 'text',
                ]
        );
        $this->addColumn(
                'billing_postcode', [
            'header' => __('ZIP/Post Code'),
            'index' => 'billing_postcode',
            
           
                ]
        );
        $this->addColumn(
                'billing_country_id', [
            'header' => __('Country'),
            'index' => 'billing_country_id',
            'type' => 'country',
                ]
        );
        $this->addColumn(
                'billing_regione', [
            'header' => __('State/Province'),
            'index' => 'billing_regione',
                ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl() {
        return $this->getUrl('emipro_ticketsystem/*/new', ['_current' => true]);
    }

    public function getRowUrl($row) {
        return $this->getUrl(
                        'emipro_ticketsystem/*/ticketcreate', ['id' => $row->getId()]
        );
    }

}
