<?php

namespace Biztech\Manufacturer\Model\Config\Source;

class FeaturedOptions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => 0,
                'label' => __('In Block')],
            ['value' => 1,
                'label' => __('In Slider')]
        ];

        return $options;
    }
}