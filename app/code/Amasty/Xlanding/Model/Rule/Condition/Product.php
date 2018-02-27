<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Model\Rule\Condition;

class Product extends AbstractCondition
{
    protected $_storeManager;

    protected $_categoryCollectionFactory;
    protected $_entityAttributeSetCollectionFactory;
    protected $_eavConfig;
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

        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $entityAttributeSetCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        array $data = []
    ){
        $this->_storeManager = $storeManager;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_entityAttributeSetCollectionFactory = $entityAttributeSetCollectionFactory;
        $this->_eavConfig = $eavConfig;
        $this->productIdLink = $productMetadata->getEdition() == 'Enterprise' ? 'row_id' : 'entity_id';

        return parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat
        );
    }

    public function getAttributeName()
    {
        if ($this->getAttribute()==='attribute_set_id') {
            return __('Attribute Set');
        }

        return $this->getAttributeObject()->getFrontendLabel();
    }

    protected function _prepareValueOptions()
    {
        if ($this->getAttribute() === 'attribute_set_id') {

            $entityTypeId = $this->_eavConfig
                ->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();

            $selectOptions = $this->_entityAttributeSetCollectionFactory->create()
                ->setEntityTypeFilter($entityTypeId)
                ->load()
                ->toOptionArray();

            $this->setData('value_select_options', $selectOptions);
        }

        return parent::_prepareValueOptions();
    }

    public function getInputType()
    {
        if ($this->getAttribute()==='attribute_set_id') {
            return 'select';
        }

        return parent::getInputType();
    }

    public function getValueElementType()
    {
        if ($this->getAttribute()==='attribute_set_id') {
            return 'select';
        }

        return parent::getValueElementType();
    }

    protected function _digCategories(array $ids)
    {
        $allIds = $ids;

        do {
            $categories = $this->_categoryCollectionFactory->create();
            $categories->addAttributeToFilter('parent_id', array('in' => $ids));
            $ids = $categories->getAllIds();
            $allIds = array_merge($allIds, $ids);
        } while ($ids);

        return $allIds;
    }

    public function getFilterValue()
    {
        $value = parent::getValue();

        if ($this->getAttributeObject()->getAttributeCode() == 'category_ids'){
            if (!is_array($value)){
                $value = [$value];
            }
            $value = $this->_digCategories($value);

        }

        return $value;
    }

    protected function _getMappingData()
    {
        $alias = $this->_getAlias();
        $dAlias = 'tad_' . $alias;

        $valueField = 'value';
        $value = $this->getFilterValue();

        $table = '';
        $mapOn = '';

        $storeId = 0;
        $fieldToTableMap = $this->_getFieldToTableMap($alias);

        if ($fieldToTableMap) {
                list($table, $mapOn, $valueField) = $fieldToTableMap;
                $table = $this->_productResource->getTable($table);
        } else {
            if (in_array($this->getAttributeObject()->getFrontendInput(), ['select', 'multiselect'], true)
            ) {
                $storeId = $this->_storeManager->getStore()->getId();
                $table = $this->_productResource->getTable('catalog_product_index_eav');
                $this->productIdLink = 'entity_id'; //there is no row_id column in this table
            } elseif ($this->getAttributeObject()->getBackendType() === \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::TYPE_STATIC) {
                $table = $this->getAttributeObject()->getBackendTable();
                $mapOn = 'search_index.entity_id = ' . $alias . '.' . $this->productIdLink;
                $valueField = $this->getAttributeObject()->getAttributeCode();
            } elseif($this->getAttributeObject()->getFrontendInput() == 'text') {
                $storeId = $this->_storeManager->getStore()->getId();
                if($this->getAttributeObject()->getScope() == \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL_TEXT) {
                    $storeId = 0;
                }
		        $table = $this->getAttributeObject()->getBackendTable();
            }
        }

        $operator  = $this->getOperatorForValidate();


        if (!$mapOn)
        {
            $mapOn = sprintf(
                'search_index.entity_id = %1$s.' . $this->productIdLink .' AND %1$s.attribute_id = %2$d AND %1$s.store_id = %3$d',
                $alias,
                $this->getAttributeObject()->getId(),
                $storeId
            );
            if ($storeId) {
                $mapOn = [$mapOn];
                $storeId = [0, $storeId];
                $alias = [$dAlias,$alias];
                array_unshift($mapOn, sprintf(
                        'search_index.entity_id = %1$s.' . $this->productIdLink . ' AND %1$s.attribute_id = %2$d AND %1$s.store_id = %3$d',
                        $alias[0],
                        $this->getAttributeObject()->getId(),
                        $storeId[0]
                ));
            }
        }

        if (!$storeId) {
            $condition =  $this->_getCondition($alias, $valueField, $operator, $value);
        } else {
            $tmp = 'tmp_xlanding_attribute';
            $eavColumn = "IFNULL(`{$alias[1]}`.`$valueField`, `{$alias[0]}`.`$valueField`)";
            $condition =$this->getOperatorCondition($tmp, $operator, $value);
            $limit = 1;
            $condition = str_replace('`' . $tmp . '`', $eavColumn, $condition, $limit);
        }

        $this->_condition = $condition;

        return [$alias, $table, $mapOn, $condition];
    }

    protected function _getCondition($alias, $valueField, $operator, $value)
    {
        return $this->getOperatorCondition("{$alias}.{$valueField}", $operator, $value);
    }

    protected function _getFieldToTableMap($alias)
    {
        $priceTable = $this->_productResource->getTable('catalog_product_index_price');
        $categoryTable = $this->_productResource->getTable('catalog_category_product_index');
        $fieldToTableMap = [
            'price' => [
                $priceTable,
                $this->_productResource->getConnection()->quoteInto(
                    'search_index.entity_id = ' . $alias . '.entity_id AND ' . $alias . '.website_id = ?',
                    $this->_storeManager->getStore()->getWebsiteId()
                ),
                'price'
            ],
            'category_ids' => [
                $categoryTable,
                'search_index.entity_id = ' . $alias . '.product_id',
                'category_id'
            ]
        ];
        return array_key_exists($this->getAttributeObject()->getAttributeCode(), $fieldToTableMap) ? $fieldToTableMap[$this->getAttributeObject()->getAttributeCode()] : null;
    }

    public function collectValidatedAttributes($select)
    {
        $fieldToTableMap = $this->_getMappingData();

        list($alias, $table, $mapOn, $this->_condition) = $fieldToTableMap;

        if (is_array($alias) && is_array($mapOn)) {
            $select->joinLeft(
                array($alias[0] => $table),
                $mapOn[0],
                array()
            );
            $select->joinLeft(
                array($alias[1] => $table),
                $mapOn[1],
                array()
            );
        } else {
            $select->joinLeft(
                array($alias => $table),
                $mapOn . ' AND ' . $this->_condition,
                array()
            );
        }

        return $this;
    }

    protected function _getAttributeCode()
    {
        return $this->getAttributeObject()->getAttributeCode();
    }

}
