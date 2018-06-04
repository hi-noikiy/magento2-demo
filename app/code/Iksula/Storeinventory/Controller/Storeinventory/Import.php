<?php
namespace Iksula\Storeinventory\Controller\Storeinventory;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;


class Import extends Action
{

    /**
     * @param Action\Context $context
     */

    protected $productCollectionfactory;

    public function __construct(Context $context 
                                , \Magento\Catalog\Model\ProductFactory  $productCollectionfactory)
    {
        $this->productCollectionfactory = $productCollectionfactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $aProductArray = array(array( 'store_id' => 'store_1' , 'sku' => '24-MB01' , 'original_price' => 10.00 , 'ecomm_price' => 11.00 , 'buffer_inventory' =>  2 , 'inventory' => 100));


        foreach($aProductArray as $Products){


            if($Products['original_price'] < $Products['ecomm_price']){

                $special_price = null;
                $original_price = $Products['original_price'];

            }else{
                $special_price = $Products['ecomm_price'];
                $original_price = $Products['original_price'];
            }

            $productLoad = $this->productCollectionfactory->create()->loadByAttribute('sku' , $Products['sku']);

            
            $productLoad->setPrice($original_price);
            $productLoad->setSpecialPrice($special_price);
            $productLoad->save();
        }

            


        /*echo '<pre>';
        print_r($productCollection->getData());
        exit;
*/


    }
}