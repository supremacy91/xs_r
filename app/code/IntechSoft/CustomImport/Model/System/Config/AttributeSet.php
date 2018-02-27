<?php

namespace IntechSoft\CustomImport\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class AttributeSet implements ArrayInterface
{

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $_attributesSet;

    /**
     * RootCategories constructor.
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory  $attributeSetFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Product\AttributeSet\Options $attributesSet
    )
    {
        $this->_attributesSet = $attributesSet;
    }


    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->getRootCategories();

        return $options;
    }

    /**
     * @return array
     */
    protected function getRootCategories()
    {
        $attributesSetArray = array();

        $items = $this->_attributesSet->toOptionArray();
        $attributesSetArray[] = 'Choose attribute set for import';

        foreach($items as $item) {
            $attributesSetArray[$item['value']]  = $item['label'];
        }

        return $attributesSetArray;
    }
}