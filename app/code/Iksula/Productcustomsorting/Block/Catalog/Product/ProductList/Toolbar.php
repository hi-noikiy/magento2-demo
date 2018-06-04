<?php
namespace Iksula\Productcustomsorting\Block\Catalog\Product\ProductList;

class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    /**
     * Get product name
     *
     * @return string
     * @codeCoverageIgnoreStart
     */
   public function setCollection($collection)
    {
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }

        if ($this->getCurrentOrder()) {
            if ($this->getCurrentOrder() == 'price_desc') {
                $this->_collection->setOrder('price', 'desc');
            } elseif ($this->getCurrentOrder() == 'price_asc') {

                $this->_collection->setOrder('price', 'asc');
            }elseif($this->getCurrentOrder() == 'product_sort_order') {
                $this->_collection->setOrder('product_sort_order', 'asc');
            }elseif($this->getCurrentOrder() == 'created_at') {

                $this->_collection->setOrder('created_at', 'desc');
            }else {
                $this->_collection->setOrder('product_sort_order', 'asc');
                //$this->_collection->setOrder('created_at', 'desc');
                //$this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
            }
        }

         
        // exit;
        return $this;
    }

    /**
 * Set collection to pager
 *
 * @param \Magento\Framework\Data\Collection $collection
 * @return $this
 */
 /*public function setCollection($collection) {

    $this->_collection = $collection;

    $this->_collection->setCurPage($this->getCurrentPage());

    // we need to set pagination only if passed value integer and more that 0
    $limit = (int)$this->getLimit();
    if ($limit) {
        $this->_collection->setPageSize($limit);
    }

    if ($this->getCurrentOrder()) {
        switch ($this->getCurrentOrder()) {
            case 'created_at':
                if ( $this->getCurrentDirection() == 'desc' ) {
                    $this->_collection
                        ->getSelect()
                        ->order('e.created_at DESC');
                } elseif ( $this->getCurrentDirection() == 'asc' ) {
                    $this->_collection
                        ->getSelect()
                        ->order('e.created_at ASC');
                }
                break;

            case 'price_desc':
                    $this->_collection
                        ->getSelect()
                        ->order('price_index.price DESC');
                break;

            case 'price_asc':
                    $this->_collection
                        ->getSelect()
                        ->order('price_index.price ASC');
                break;

            default:
                $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
                break;

        }

    }

}*/

}
