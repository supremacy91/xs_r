<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Model\Rule\Condition;

class IsNewByPeriod extends AbstractCondition
{
    protected $_inputType = 'select';
    protected $_string;
    protected $_storeManager;
    protected $_dateTime;
    protected $_date;
    private $productIdLink;
    private $aliasFrom;
    private $dAliasFrom;
    private $aliasTo;
    private $dAliasTo;

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
    ){
        $this->_string = $string;
        $this->_storeManager = $storeManager;

        $this->_dateTime = $dateTime;
        $this->_date = $date;

        $this->productIdLink = $productMetadata->getEdition() == 'Enterprise' ? 'row_id' : 'entity_id';


        $alias = $this->_getAlias();
        $this->aliasFrom = $alias . '_from';
        $this->dAliasFrom = 'tad_' . $this->aliasFrom;
        $this->aliasTo = $alias . '_to';
        $this->dAliasTo = 'tad_' . $this->aliasTo;

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
        return __('Is New');
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
        return 'news_by_period';
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
        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        $tmp = 'tmp_xlanding_news';
        $fieldFrom = new \Zend_Db_Expr('IFNULL(' . $this->aliasFrom . '.value,' . $this->dAliasFrom . '.value)');
        $fieldTo = new \Zend_Db_Expr('IFNULL(' . $this->aliasTo . '.value,' . $this->dAliasTo . '.value)');

        $conditionFrom = $this->getOperatorCondition($tmp, '<=', $todayEndOfDayDate);
        $limit = 1;
        $conditionFrom = str_replace('`' . $tmp . '`', $fieldFrom, $conditionFrom, $limit);

        $conditionTo = $this->getOperatorCondition($tmp, '>=', $todayStartOfDayDate);
        $limit = 1;
        $conditionTo = str_replace('`' . $tmp . '`', $fieldTo, $conditionTo, $limit);

        if ((bool)$this->getValue() ^ $this->getOperatorForValidate() == '==') {
            //negative condition
            $this->_condition = "!( $conditionFrom ) OR !( $conditionTo ) OR ( $fieldFrom  IS NULL) OR ( $fieldTo  IS NULL)";
        } else {
            $this->_condition = '(' . $conditionFrom . ') and (' . $conditionTo . ')';
        }

        $this->join($select);
    }

    protected function join($select)
    {
        $attributeFrom = $this->_config->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'news_from_date');
        $attributeTo = $this->_config->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'news_to_date');
        $mapTpl = 'search_index.entity_id = %1$s.' . $this->productIdLink . ' AND %1$s.attribute_id = %2$d AND %1$s.store_id = %3$d';
        $storeId = $this->_storeManager->getStore()->getId();

        $select->joinLeft(
            [
                $this->dAliasFrom => $this->_productResource->getTable('catalog_product_entity_datetime')
            ],
            sprintf(
                $mapTpl,
                $this->dAliasFrom,
                $attributeFrom->getId(),
                0
            ),
            []
        );
        $select->joinLeft(
            [
                $this->aliasFrom => $this->_productResource->getTable('catalog_product_entity_datetime')
            ],
            sprintf(
                $mapTpl,
                $this->aliasFrom,
                $attributeFrom->getId(),
                $storeId
            ),
            []
        );

        $select->joinLeft(
            [
                $this->dAliasTo => $this->_productResource->getTable('catalog_product_entity_datetime')
            ],
            sprintf(
                $mapTpl,
                $this->dAliasTo,
                $attributeTo->getId(),
                0
            ),
            []
        );
        $select->joinLeft(
            [
                $this->aliasTo => $this->_productResource->getTable('catalog_product_entity_datetime')
            ],
            sprintf(
                $mapTpl,
                $this->aliasTo,
                $attributeTo->getId(),
                $storeId
            ),
            []
        );
    }
}
