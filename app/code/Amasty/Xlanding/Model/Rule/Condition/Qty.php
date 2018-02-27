<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Model\Rule\Condition;

class Qty extends AbstractCondition
{
    protected $_inputType = 'numeric';

    public function getAttributeElementHtml()
    {
        return __('Qty');
    }

    protected function _getAttributeCode()
    {
        return 'qty';
    }

    public function collectValidatedAttributes($select)
    {
        $alias = $this->_getAlias();

        $value     = $this->getValue();
        $operator  = $this->getOperatorForValidate();

        $this->_condition = $this->getOperatorCondition($alias . '.qty', $operator, $value);

        $select->joinLeft(
            [
                $alias => $this->_productResource->getTable('cataloginventory_stock_item')
            ],
            'search_index.entity_id = ' . $alias . '.product_id and ' . $this->_condition,
            []
        );
    }
}
