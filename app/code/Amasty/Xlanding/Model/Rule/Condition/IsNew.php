<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Model\Rule\Condition;

class IsNew extends AbstractCondition
{
    protected $_inputType = 'select';
    protected $_string;
    protected $_storeManager;
    protected $_dateTime;
    protected $_date;
    private $productIdLink;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        $this->_string = $string;
        $this->_storeManager = $storeManager;

        $this->_dateTime = $dateTime;
        $this->_date = $date;

        $this->productIdLink = $productMetadata->getEdition() == 'Enterprise' ? 'row_id' : 'entity_id';
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data
        );
    }


    public function getAttributeElementHtml()
    {
        return __('Is New by \'is_new\' attribute');
    }

    public function getInputType()
    {
        return 'select';
    }

    public function getValueElementType()
    {
        return 'select';
    }

    protected function _getAttributeCode()
    {
        return 'is_new';
    }

    protected function _prepareValueOptions()
    {
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');

        $selectOptions = [
            ['value' => 1, 'label' => 'Yes'],
            ['value' => 0, 'label' => 'No']
        ];

        $this->_setSelectOptions($selectOptions, $selectReady, $hashedReady);

        return $this;
    }

    public function collectValidatedAttributes($select)
    {
        $alias = $this->_getAlias();
        $value     = $this->getValue();
        $operator  = $this->getOperatorForValidate();

        $attribute = $this->_config->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'new');

        $mapTpl = 'search_index.entity_id = %1$s.' . $this->productIdLink
            . ' AND %1$s.attribute_id = %2$d AND %1$s.store_id = %3$d and %4$s';

        $this->_condition = $this->getOperatorCondition($alias . '.value', $operator, $value);

        $select->joinLeft(
            [
                $alias => $this->_productResource->getTable('catalog_product_entity_int')
            ],
            sprintf(
                $mapTpl,
                $alias,
                $attribute->getId(),
                0,
                $this->_condition
        ),
            []
        );
    }
}
