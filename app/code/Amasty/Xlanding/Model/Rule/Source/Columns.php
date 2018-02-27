<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Xlanding\Model\Rule\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Columns implements OptionSourceInterface
{
    const THREE_COL_MODE = 3;
    const FOUR_COL_MODE = 4;
    const FIVE_COL_MODE = 5;


    public function toOptionArray()
    {
        return [
            ['value' => self::THREE_COL_MODE, 'label' => self::THREE_COL_MODE],
            ['value' => self::FOUR_COL_MODE, 'label' => self::FOUR_COL_MODE],
            ['value' => self::FIVE_COL_MODE, 'label' => self::FIVE_COL_MODE],
        ];
    }
}
