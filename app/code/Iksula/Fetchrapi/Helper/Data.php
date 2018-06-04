<?php

namespace Iksula\Fetchrapi\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

       protected $fetchrfactory;
       protected $scopeConfigObject;

        public function __construct(
                                    \Iksula\Fetchrapi\Model\FetchrFactory  $fetchrfactory,
                                    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigObject
                                    ){
            $this->fetchrfactory = $fetchrfactory;
            $this->scopeConfigObject = $scopeConfigObject;
        }

        public function createTrackingNofetchrapi($data = NULL , $ordersplitid){

                  $url = trim($this->scopeConfigObject->getValue('fetchr_config_main/fetchr_config/fetchr_url_order_create'));


                  $datetimestamp = date( "Y-m-d H:i:s",mktime(0, 0, 0));

                  $data = json_encode($data);

                  $url=$url;

                  $ch = curl_init($url);
                  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                  'Content-Type: application/json',
                  'Content-Length: ' . strlen($data))
                  );
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                  curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
                  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                  $result = curl_exec($ch);
                  curl_close($ch);
                  try{
                    $this->fetchrfactory->create()
                        ->setRequest($data)
                        ->setResponse($result)
                        ->setOrderSplitId($ordersplitid)
                        ->setCreatedDate($datetimestamp)
                        ->save();

                }catch(Exception $e){
                  echo $e->getMessage();
                }

                  return $result;
        }


}
