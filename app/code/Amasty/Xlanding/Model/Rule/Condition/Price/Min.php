<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Model\Rule\Condition\Price;

class Min extends AbstractPrice
{
    public function getAttributeElementHtml()
    {
        return __('Min Price');
    }

    protected function _getAttributeCode()
    {
        return 'min_price';
    }
}