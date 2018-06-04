<?php
namespace Iksula\EmailTemplate\Mail\Template;

/*class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    public function addAttachment(
        $body,
        $mimeType    = \Zend_Mime::TYPE_OCTETSTREAM,
        $disposition = \Zend_Mime::DISPOSITION_ATTACHMENT,
        $encoding    = \Zend_Mime::ENCODING_BASE64,
        $filename    = null
    ) {
        $this->message->createAttachment($body, $mimeType, $disposition, $encoding, $filename);
        return $this;
    }
}

namespace Iksula\EmailTemplate1\Model\Mail; */
 
class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * @param Api\AttachmentInterface $attachment
     */
    public function addAttachment($pdfString, $file_name)
    {
        $this->message->createAttachment(
            $pdfString,
            'application/pdf',
            \Zend_Mime::DISPOSITION_ATTACHMENT,
            \Zend_Mime::ENCODING_BASE64,
            $file_name
        );
        return $this;
    }
}