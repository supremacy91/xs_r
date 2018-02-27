<?php

namespace Biztech\Manufacturer\Model\Config\Source;

class Attributes implements \Magento\Framework\Option\ArrayInterface
{
    protected $_productAttributes;
    protected $_storeManager;
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $interface,
        \Magento\Catalog\Model\Product $productAttributes,
        \Biztech\Manufacturer\Model\Config $config
    ){
        $this->objectManager = $interface;
        $this->_productAttributes = $productAttributes->getAttributes();
        $this->_storeManager = $config->getStoreManager();
    }

    public function toOptionArray()
    {
        $allAttributes = [];
        foreach ($this->_productAttributes as $productAttribute) {
            if ($productAttribute->getFrontend()->getInputType() == 'select') {
                $allAttributes[] = ['label' => $productAttribute->getAttributeCode(), 'value' => $productAttribute->getAttributeCode()];
            }
        }
        return $allAttributes;
    }
}