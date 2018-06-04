<?php

namespace Emipro\Ticketsystem\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Emipro\Ticketsystem\Model\TicketSystemFactory;
use Emipro\Ticketsystem\Model\TicketConversationFactory;
use Magento\Framework\Model\AbstractModel;
use Emipro\Ticketsystem\Helper\Data;
use Emipro\Ticketsystem\Helper\Email as EmailHelper;
use Magento\Framework\Model\Context;
use Magento\Store\Model\StoreManager;

class Mails extends AbstractModel {

    protected $_helper;
    protected $_emailHelper;
    protected $_storeManager;
    protected $_encryptor;


    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        EmailHelper $emailHelper,
        StoreManager $storeManager,
        Data $helper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor

    ) {

        $this->_helper = $helper;
        $this->_encryptor = $encryptor;
        $this->_emailHelper = $emailHelper;
        $this->context = $context;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry);
    }

    public function fetchMails() {
        
        $websites = $this->_storeManager->getWebsites();
        foreach ($websites as $website) {
        $storeId = $website->getDefaultStore()->getId();
        
		$this->_helper->getConfig("emipro/emipro_emailgateway/enable",$storeId);
            if ($this->_helper->getConfig("emipro/emipro_emailgateway/enable",$storeId)) {
                
                $host = $this->_helper->getConfig("emipro/emipro_emailgateway/host",$storeId);
                $port = $this->_helper->getConfig("emipro/emipro_emailgateway/port",$storeId);
                $email = $this->_helper->getConfig("emipro/emipro_emailgateway/email",$storeId);
                $password = $this->_helper->getConfig("emipro/emipro_emailgateway/password",$storeId);
                $password = $this->_encryptor->decrypt($password);
                if ($this->_helper->getConfig("emipro/emipro_emailgateway/encryption")) {
                    $hostname = '{' . $host . ':' . $port . '/imap/ssl}INBOX';
                } else {
                    $hostname = '{' . $host . ':' . $port . '/novalidate-cert}INBOX';
                }
                $emails = $this->_emailHelper->getMails($hostname, $email, $password);
                if (count($emails)) {
                    $this->_emailHelper->getMessage($emails, $storeId);
                }
            }
        }
    }

}
