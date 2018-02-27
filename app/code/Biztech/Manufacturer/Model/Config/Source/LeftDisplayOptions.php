<?php

namespace Biztech\Manufacturer\Model\Config\Source;

class LeftDisplayOptions implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $options = [
            ['value' => 0,
                'label' => __('Logo and Name')],
            ['value' => 1,
                'label' => __('Only Name')],
            ['value' => 2,
                'label' => __('Only Logo')]
        ];

        return $options;
    }
}