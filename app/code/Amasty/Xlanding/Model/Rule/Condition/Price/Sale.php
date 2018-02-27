<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Model\Rule\Condition\Price;

class Sale extends AbstractPrice
{
    protected $_inputType = 'select';

    public function getAttributeElementHtml()
    {
        return __('Is on Sale');
    }

    protected function _getAttributeCode()
    {
        return 'sale';
    }

    public function getInputType()
   {
       return 'select';
   }

   public function getValueElementType()
   {
       return 'select';
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

    protected function _getCondition()
    {
        if (!$this->_condition) {
            $alias = $this->_getAlias();

            $value     = $this->getValue();
            $operator  = $this->getOperatorForValidate();

            if ($value && $operator == '=='){
                $this->_condition = $alias . '.final_price < ' . $alias . '.price';
            } else {
                $this->_condition = $alias . '.final_price >= ' . $alias . '.price';
            }
        }
        return $this->_condition;
    }
}