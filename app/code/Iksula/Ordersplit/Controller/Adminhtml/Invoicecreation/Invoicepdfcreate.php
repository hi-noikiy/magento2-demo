<?php
namespace Iksula\Ordersplit\Controller\Adminhtml\Invoicecreation;
//use lib\internal\TCPDF\TCPDF;
use TCPDF_TCPDF;



use Magento\Framework\App\Filesystem\DirectoryList;

class Invoicepdfcreate extends \Magento\Backend\App\Action  {




    public function execute() {

 $baseDir = DirectoryList::MEDIA;

$tcpdf = new TCPDF_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information

        // $tcpdf->SetCreator(PDF_CREATOR);
        //
        // $tcpdf->SetAuthor('Nicola Asuni');
        //
        // $tcpdf->SetTitle('TCPDF Example 001');
        //
        // $tcpdf->SetSubject('TCPDF Tutorial');
        //
        // $tcpdf->SetKeywords('TCPDF, PDF, example, test, guide');


// set default header data

        $Header_title = 'Sample PDF';

        $tcpdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . $Header_title, PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));

        $tcpdf->setFooterData(array(0, 64, 0), array(0, 64, 128));


// set header and footer fonts

        $tcpdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));

        $tcpdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));


// set default monospaced font

        $tcpdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);


// set margins

        $tcpdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

        $tcpdf->SetHeaderMargin(PDF_MARGIN_HEADER);

        $tcpdf->SetFooterMargin(PDF_MARGIN_FOOTER);


// set auto page breaks

        $tcpdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);


// set image scale factor

        $tcpdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


// set some language-dependent strings (optional)

        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {

            require_once(dirname(__FILE__) . '/lang/eng.php');

            $tcpdf->setLanguageArray($l);

        }


// ---------------------------------------------------------

// set default font subsetting mode

        $tcpdf->setFontSubsetting(true);

//your htmls here

$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Invoice</title>
</head>

<body>
<table cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse; margin: 0 auto; text-align: left; width: 660px;font-family:Arial,sans-serif;font-size: 12px;">
  <tr>
    <td colspan="6" style="padding-bottom:30px;"><img src="logo2.jpg" width="100" /></td>
  </tr>
  <tr>
    <td colspan="2" valign="top" width="20%" style="font-size:14px;">
    <p style="margin:0 0 10px 0;"><strong>Customer Details</strong></p>
    <p style="margin:0 0 5px 0;">#4H</p>
    <p style="margin:0 0 5px 0;">Al Mass Tower</p>
    <p style="margin:0 0 5px 0;">Jumaeira Lake Towers</p>
    <p style="margin:0 0 5px 0;">Dubai United Arab Emirates</p>
    <p style="margin:0 0 5px 0;">Mobile Number: 055 3245169</p>
    <p style="margin:0 0 5px 0;">Email: XXXX@gmail.com</p></td>

    <td colspan="2" valign="top" width="40%" style="text-align:center; font-size:20px;"><strong>INVOICE</strong></td>
    <td colspan="2" valign="top" width="40%;" style="font-size:14px;">
    <p style="margin:0 0 5px 0;"><strong>Invoice Date:</strong>4-jun-2017</p>
      <p style="margin:0 0 5px 0;"><strong>Invoice Number:</strong> 10159820</p>
      <p style="margin:0 0 5px 0;"><strong>AWB Number:</strong></p>
    <p style="margin:0 0 5px 0;">16091161419998</p></td>
  </tr>
  <tr>
    <td colspan="6" style="padding:20px 0; font-size:15px;"><p style="margin:0;"><strong>ORDER ID: 10159820 </strong></p></td>
  </tr>
  <tr style="text-align:center">
    <td style="border-top:1px solid #000; border-bottom:1px solid #000; padding:5px 0; font-size:14px;">&nbsp;</td>
    <td style="border-top:1px solid #000; border-bottom:1px solid #000; padding:5px 0; font-size:14px;text-align:left;">Item Details</td>
    <td style="border-top:1px solid #000; border-bottom:1px solid #000; padding:5px 0; font-size:14px;">Qty</td>
    <td style="border-top:1px solid #000; border-bottom:1px solid #000; padding:5px 0; font-size:14px;">Unit Price</td>
    <td style="border-top:1px solid #000; border-bottom:1px solid #000; padding:5px 0; font-size:14px;">Discount</td>
    <td style="border-top:1px solid #000; border-bottom:1px solid #000; padding:5px 0; font-size:14px;">Amount</td>
  </tr>
  <tr style="text-align:center">
    <td style="padding:10px 15px 10px 0;border-bottom:1px solid #000;"></td>
    <td valign="top" style="padding:10px 0;border-bottom:1px solid #000;text-align:left;">
    <p style="margin:0 0 5px 0">Sub Order: 4104832</p>
    <p style="margin:0 0 5px 0">Product Name: Lorem Ipsum is simply dummy text</p>
    <p style="margin:0 0 5px 0">Item Code: 810038383839</p>
    <p style="margin:0 0 5px 0">Color: Silver</p></td>
    <td style="padding:10px 0;border-bottom:1px solid #000;">1</td>
    <td style="padding:10px 0;border-bottom:1px solid #000;">1000</td>
    <td style="padding:10px 0;border-bottom:1px solid #000;">20</td>
    <td style="padding:10px 0;border-bottom:1px solid #000;">980</td>
  </tr>
  <tr style="text-align:center">
    <td style="padding:10px 15px 10px 0;border-bottom:1px solid #000;"></td>
    <td valign="top" style="padding:10px 0;border-bottom:1px solid #000;text-align:left;">
    <p style="margin:0 0 5px 0">Sub Order: 4104832</p>
    <p style="margin:0 0 5px 0">Product Name: Lorem Ipsum is simply dummy text</p>
    <p style="margin:0 0 5px 0">Item Code: 810038383839</p>
    <p style="margin:0 0 5px 0">Color: Silver</p>
    </td>
    <td style="padding:10px 0;border-bottom:1px solid #000;">1</td>
    <td style="padding:10px 0;border-bottom:1px solid #000;">1999</td>
    <td style="padding:10px 0;border-bottom:1px solid #000;">100</td>
    <td style="padding:10px 0;border-bottom:1px solid #000;">1899</td>
  </tr>
  <tr style="text-align:center">
    <td style="padding:10px 15px 10px 0;border-bottom:1px solid #000;"></td>
    <td valign="top" style="padding:10px 0;border-bottom:1px solid #000;text-align:left;">
    <p style="margin:0 0 5px 0">Sub Order: 4104832</p>
    <p style="margin:0 0 5px 0">Product Name: Lorem Ipsum is simply dummy text</p>
    <p style="margin:0 0 5px 0">Item Code: 810038383839</p>
    <p style="margin:0 0 5px 0">Color: Silver</p>
    </td>
    <td style="padding:10px 0;border-bottom:1px solid #000;">1</td>
    <td style="padding:10px 0;border-bottom:1px solid #000;">1999</td>
    <td style="padding:10px 0;border-bottom:1px solid #000;">100</td>
    <td style="padding:10px 0;border-bottom:1px solid #000;">1899</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td style="padding:20px 0 5px 0; font-size:14px;"><strong>Product Subtotal:</strong></td>
    <td style="padding:20px 0 5px 0; font-size:14px;">AED 3679.00</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td style="font-size:14px;padding:0 0 5px 0;"><strong>Shipping Charges:</strong></td>
    <td style="font-size:14px;padding:0 0 5px 0;">AED 250.00</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td style="font-size:14px;padding:0 0 5px 0;"><strong>VAT:</strong></td>
    <td style="font-size:14px;padding:0 0 5px 0;">AED 150.00</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td style="font-size:14px;padding:0 0 20px 0;"><strong>Discount:</strong></td>
    <td style="font-size:14px;padding:0 0 20px 0;">-AED 200.00</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td style="font-size:16px;border-top:1px solid #000;padding:10px 0 0 0;"><strong>Grand Total:</strong></td>
    <td style="font-size:16px;border-top:1px solid #000;padding:10px 0 0 0;"><strong>AED 3879.00</strong></td>
  </tr>
  <tr>
    <td colspan="6" style="text-align:center; padding:40px 0 20px 0;">
    <p style="margin:0 0 5px 0; font-size:14px;">Toll Free: 800 2XL (295) | www.2xlme.com | E-mail: customercare@2xlme.com</p>
    <p style="margin:0 0 5px 0">DUBAI <span style="padding:0 5px;">&bull;</span> ABU DHABI <span style="padding:0 5px;">&bull;</span> AL AIN <span style="padding:0 5px;">&bull;</span> SHARJAH <span style="padding:0 5px;">&bull;</span> FUJAIRAH</p>
    <p style="margin:0">
      <a href="#" style="margin:0 5px;"></a>
      <a href="#" style="margin:0 5px;"></a>
      <a href="#" style="margin:0 5px;"></a>
    </p></td>
  </tr>
</table>
</body>
</html>
' ;

// set some language dependent data:

        $lg = Array();

        $lg['a_meta_charset'] = 'UTF-8';


        $tcpdf->setLanguageArray($lg);


// set font

//dejavusans & freesans For Indian Rupees symbol

        $tcpdf->SetFont('freesans', '', 12);

        // remove default header/footer

//$tcpdf->setPrintHeader(false);

        $tcpdf->setPrintFooter(false);


        $tcpdf->AddPage();


        $tcpdf->writeHTML($html, true, false, true, false, '');


        $tcpdf->lastPage();



//$tcpdf->Output('report_per_route.pdf', 'I');

        //$this->logger->debug('report_per_route');

        $filename = 'Sample13.pdf';


        $tcpdf->Output(__DIR__ .'/'.$filename, 'F');


        echo 'test 1';

   }

}


?>
