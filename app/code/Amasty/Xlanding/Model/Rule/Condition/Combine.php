<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Model\Rule\Condition;

class Combine extends \Magento\CatalogRule\Model\Rule\Condition\Combine
{
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Amasty\Xlanding\Model\Rule\Condition\ProductFactory $conditionFactory,
        array $data = []
    ) {

        parent::__construct($context, $conditionFactory, $data);

        $this->setType('Amasty\Xlanding\Model\Rule\Condition\Combine');
    }

    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_productFactory->create()->loadAttributeOptions()->getAttributeOption();

        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Amasty\Xlanding\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }
        $conditions = [['value' => '', 'label' => __('Please choose a condition to add.')]];
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => 'Amasty\Xlanding\Model\Rule\Condition\Combine',
                    'label' => __('Conditions Combination'),
                ],
                [
                    'label' => __('Custom Fields'),
                    'value' => array(
                        array(
                            'label' => __('Is New (by a period)'),
                            'value' => 'Amasty\Xlanding\Model\Rule\Condition\IsNewByPeriod',
                        ),
                        array(
                            'label' => __('Is New (by \'is_new\' attribute)'),
                            'value' => 'Amasty\Xlanding\Model\Rule\Condition\IsNew',
                        ),
                        array(
                            'label' => __('Created (in days)'),
                            'value' => 'Amasty\Xlanding\Model\Rule\Condition\Created',
                        ),
                        array(
                            'label' => __('In Stock'),
                            'value' => 'Amasty\Xlanding\Model\Rule\Condition\InStock',
                        ),
                        array(
                            'label' => __('Is on Sale'),
                            'value' => 'Amasty\Xlanding\Model\Rule\Condition\Price\Sale',
                        ),
                        array(
                            'label' => __('Qty'),
                            'value' => 'Amasty\Xlanding\Model\Rule\Condition\Qty',
                        ),
                        array(
                            'label' => __('Min Price'),
                            'value' => 'Amasty\Xlanding\Model\Rule\Condition\Price\Min',
                        ),
                        array(
                            'label' => __('Max Price'),
                            'value' => 'Amasty\Xlanding\Model\Rule\Condition\Price\Max',
                        ),
                        array(
                            'label' => __('Final Price'),
                            'value' => 'Amasty\Xlanding\Model\Rule\Condition\Price\FinalPrice',
                        ),
                    )
                ],
                ['label' => __('Product Attribute'), 'value' => $attributes]
            ]
        );

        return $conditions;
    }

    public function collectConditionSql()
    {
        $wheres = [];
        foreach ($this->getConditions() as $condition)
        {
            $wheres[] = $condition->collectConditionSql();
        }

        if (empty($wheres)) {
            return '';
        }

        $delimiter = $this->getAggregator() == "all" ? ' AND ' : ' OR ';
        return '(' . implode($delimiter, $wheres) . ')';
    }
}
