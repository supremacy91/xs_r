<?php

namespace Biztech\Manufacturer\Observer;

use Magento\Framework\Event\ObserverInterface;

class assignSourceModel implements ObserverInterface
{

    protected $_storeConfig;
    protected $_eavConfig;
    protected $_eavAttributeModel;

    public function __construct(
        \Biztech\Manufacturer\Model\Config $config,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\Attribute $eavAttributeModel
    )
    {

        $this->_storeConfig = $config;
        $this->_eavConfig = $eavConfig;
        $this->_eavAttributeModel = $eavAttributeModel;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $attribute = $this->_storeConfig->getCurrentStoreConfigValue('manufacturer/general/brandlist_attribute');
        $attributeId = $this->_eavConfig->getAttribute('catalog_product', $attribute)->getAttributeId();
        $sourceModel = 'Biztech\Manufacturer\Model\Entity\Attribute\Source\Manufacturer';

        foreach ($this->_eavAttributeModel->getCollection() as $eavAttribute) {
            if( $eavAttribute->hasSourceModel() && $eavAttribute->getSourceModel() === $sourceModel ){
                $eavAttribute->setSourceModel(null)->save();
            }
        }

        $attributeModel = $this->_eavAttributeModel->load($attributeId);
        $attributeModel->setSourceModel($sourceModel)->save();
    	return;
    }
}