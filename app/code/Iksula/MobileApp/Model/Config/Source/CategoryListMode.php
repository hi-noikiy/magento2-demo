<?php
/**
 * Copyright © 2016 Oscprofessionals® All Rights Reserved.
 */
namespace Iksula\MobileApp\Model\Config\Source;

class CategoryList implements \Magento\Framework\Option\ArrayInterface
{
 public function toOptionArray()
 {
  return [
    ['value' => 'grid', 'label' => __('Grid Only')],
    ['value' => 'list', 'label' => __('List Only')],
    ['value' => 'grid-list', 'label' => __('Grid (default) / List')],
    ['value' => 'list-grid', 'label' => __('List (default) / Grid')]
  ];
 }
}