<?php

namespace Iksula\EmailTemplate\Helper;
use \Magento\Framework\App\Helper\AbstractHelper as CoreHelper;

class Email extends CoreHelper
{
    protected $_scopeConfig;
    protected $_storeManager;
    protected $inlineTranslation;
    protected $_transportBuilder;
    protected $_logger;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->_scopeConfig = $context;
        $this->_logger = $logger;
        $this->_storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        parent::__construct($context, $data);
    }

    /**
     * Return store
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    public function generateTemplate($templateId,$emailTemplateVariables,$senderInfo,$receiverInfo,$attachment,$file_name)
    {
        //file content is attached
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //$directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
        //$file  =  $directory->getRoot()."/COPYING.txt";
        $file  =  $attachment."$file_name";


        if( strpos($receiverInfo['email'] , ',') !== false ) {
           $RecieverEmails = explode(',' , $receiverInfo['email']);
        }else{
           $RecieverEmails = $receiverInfo['email'];
        }

        if( strpos($senderInfo['email'] , ',') !== false ) {
           $aSenderInfo = explode(',' , $senderInfo['email']);
           $senderInfo['email'] = $aSenderInfo[0];
        }else{
           $senderInfo['email'] = $senderInfo['email'];
        }


        if($file_name != '')
        {
            //$attachment = file_get_contents($file);
            $template =  $this->_transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->_storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom($senderInfo)
                ->addTo($RecieverEmails,$receiverInfo['name'])
                ->addAttachment(file_get_contents($file), $file_name);
        }
        else
        {
            $template =  $this->_transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->_storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom($senderInfo)
                ->addTo($RecieverEmails,$receiverInfo['name'])
                ->addBcc('balvinder.singh@2xlme.com');
        }
        return $this;


    }



    /* your send mail method*/
    public function emailTemplate($templateId,$emailTemplateVariables,$senderInfo,$receiverInfo,$attachment,$file_name)
    {

        $level = 'DEBUG';
        $this->_logger->log($level,'errorlog=email',array($emailTemplateVariables, array('templateId'=>$templateId), $senderInfo, $receiverInfo));

        $this->inlineTranslation->suspend();
        $this->generateTemplate($templateId,$emailTemplateVariables,$senderInfo,$receiverInfo,$attachment,$file_name);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }

    public function smsTemplate($template_type, $data, $number){
        $password = $this->scopeConfig->getValue('sms_configuration/sms_setting/pswd');
        $username = $this->scopeConfig->getValue('sms_configuration/sms_setting/username');
        // $senderid = urlencode($this->scopeConfig->getValue('sms_configuration/sms_setting/senderid'));
        $senderid = $this->scopeConfig->getValue('sms_configuration/sms_setting/senderid');
        $api_url = $this->scopeConfig->getValue('sms_configuration/sms_setting/api_url');

        $data_arrray = '';
        $data_key = array_keys($data);
        for($i=0;$i<count($data_key);$i++)
        {
            $data_arrray .= "{".$data_key[$i]."},";
        }
        $key = rtrim($data_arrray, ",");

        $key = explode(",",$key);

        $data_values_arrray = '';
        $data_values = array_values($data);
        for($j=0;$j<count($data_values);$j++)
        {
            $data_values_arrray .= $data_values[$j].",";
        }
        $values = rtrim($data_values_arrray, ",");
        $values = explode(",",$values);
        $numbers = '';
        $text = str_replace($key, $values, $template_type);
        //$template = urlencode($text);
        $template = $text;
        if(is_array($number))
        {
            for($i=0; $i<count($number); $i++)
            {
                $numbers .= '{"mobileNumber":"'.$number[$i].'"}, ';
            }
        }
        else
            $numbers = '{"mobileNumber":'.$number.'}';

        //$number_value = json_decode('{"messageParams":[{"mobileNumber":"919819406068"}]}', true);
        $number_value = json_decode('{"messageParams":['.rtrim($numbers,', ').']}', true);

        $data = array("userName" => $username, "priority" => 0, "referenceId" => "124154324", "dlrUrl" => null,"msgType" => 0, "senderId" => $senderid, "message" => $template,"mobileNumbers"=>$number_value,"password" => $password);
        $data_string = json_encode($data);
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json' ,
            'Content-Length: ' . strlen($data_string))
        );
        $level = 'DEBUG';
        $result = curl_exec($ch);
        $this->_logger->log($level,'errorlog=number',array( array('number'=>$number,'template'=>$template,'result'=>$result)));
    }
}
