<?php

namespace Biztech\Manufacturer\Model\Config\Source;

class Enabledisable implements \Magento\Framework\Option\ArrayInterface
{
    protected $_helper;

    /**
     * Enabledisable constructor.
     * @param \Magento\Framework\ObjectManagerInterface $interface
     * @param \Biztech\Manufacturer\Helper\Data $helperdata
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $interface,
        \Biztech\Manufacturer\Helper\Data $helperdata
    )
    {
        $this->objectManager = $interface;
        $this->_helper = $helperdata;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => 0, 'label' => __('No')],
        ];
        $websites = $this->_helper->getAllWebsites();
        if (!empty($websites)) {
            $options[] = ['value' => 1, 'label' => __('Yes')];
        }
        return $options;
    }
}