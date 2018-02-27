<?php

namespace Biztech\Manufacturer\Block\Manufacturer;

use Biztech\Manufacturer\Block\BaseBlock;
use Magento\Framework\View\Element\Template\Context;
use Biztech\Manufacturer\Model\Config;
use Biztech\Manufacturer\Helper\Data;

class Form extends \Magento\Framework\View\Element\Template
{
    protected $_storeConfig;
    protected $_eavConfig;
    protected $_helperData;

    public function __construct(
        Context $context,
        Config $config,
        Data $helperData,
        \Magento\Eav\Model\Config $eavConfig
    )
    {
        $this->_storeConfig = $config;
        $this->_eavConfig = $eavConfig;
        $this->_helperData = $helperData;
        parent::__construct($context);
    }

    public function getStoreConfig()
    {
        return $this->_storeConfig;
    }

    public function getManufacturer()
    {
        $attribute = $this->getStoreConfig()->getCurrentStoreConfigValue('manufacturer/general/brandlist_attribute');
        $attributes = $this->_eavConfig->getAttribute('catalog_product', $attribute);
        $attributeOptions = $attributes->getSource()->getAllOptions(false);
        $manufacturer = $this->_helperData->getManufacturerCollection()->getData();
        $options = [];
        foreach ($manufacturer as $key => $value) {
            $options[] = [
                'label' => $value['brand_name'],
                'value' => $value['manufacturer_id']
            ];
        }
        return $options;
    }
}
