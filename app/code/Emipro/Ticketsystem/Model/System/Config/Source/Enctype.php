<?php

namespace Emipro\Ticketsystem\Model\System\Config\Source;

class Enctype extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface {

    public function toOptionArray() {
        $options = [
            [
                "value" => 0,
                "label" => __("NONE")
            ],
            [
                "value" => 1,
                "label" => __("SSL")
            ]
        ];
        return $options;
    }

}
