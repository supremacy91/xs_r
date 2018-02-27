<?php

namespace Biztech\Manufacturer\Model\Validate;

use Magento\Framework\Exception\LocalizedException;

class Options extends \Magento\Framework\App\Config\Value
{
    public function afterSave()
    {
        $widthHeight = $this->getValue();
        if (!preg_match("#^(\\d+x{1}\\d+)$#", $widthHeight)) {
            throw new LocalizedException(
                __("Please add dimension in proper format for all 'Width x Height' fields. For eg '30x30'")
            );
        }
        $this->_cacheManager->clean();
        return parent::afterSave();
    }
}
