<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Model\Rule\Condition\Price;

class Max extends AbstractPrice
{
    public function getAttributeElementHtml()
    {
        return __('Max Price');
    }

    protected function _getAttributeCode()
    {
        return 'max_price';
    }
}